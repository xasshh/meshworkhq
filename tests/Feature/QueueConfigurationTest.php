<?php

use App\Models\Brief;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Support\Facades\DB;

/**
 * The wave system is the product: a brief reaches ten professionals now, the
 * next fifteen in six hours, the rest in twenty four. That only happens if
 * delayed jobs are actually held and run later, which needs a real queue
 * connection and a worker process. Running sync executes a delayed job inline
 * and immediately, so every wave fires at once and the staggering silently
 * stops existing. These tests cover both halves: that the delays are real, and
 * that the deployed configuration can honour them.
 */
it('holds waves 2 and 3 as future queue jobs instead of running them inline', function () {
    config(['queue.default' => 'database']);

    User::factory()->alertReady()->count(30)->create([
        'skill_tags' => ['Brand Identity'],
    ]);

    $brief = Brief::factory()->published()->create([
        'skill_tags' => ['Brand Identity'],
        'is_remote' => true,
    ]);

    app(AlertService::class)->initiate($brief);

    $jobs = DB::table('jobs')->orderBy('available_at')->get();

    expect($jobs)->toHaveCount(3);

    $offsets = $jobs->map(fn ($job): int => (int) round(($job->available_at - now()->getTimestamp()) / 3600));

    expect($offsets->all())->toBe([0, 6, 24]);
});

it('does not deploy with a sync queue connection', function () {
    $fly = file_get_contents(base_path('fly.toml'));

    expect($fly)->not->toMatch('/QUEUE_CONNECTION\s*=\s*.sync./')
        ->and($fly)->toMatch('/QUEUE_CONNECTION\s*=\s*.database./');
});

it('runs a queue worker and the scheduler in the deployed container', function () {
    $supervisor = base_path('.fly/supervisor/conf.d');

    expect(file_get_contents($supervisor.'/worker.conf'))
        ->toContain('[program:worker]')
        ->toContain('queue:work')
        // The fan-out that decides who hears about a brief comes first.
        ->toContain('--queue=alerts,default');

    // cron is installed in the image and the crontab written, but the daemon
    // has to be started or the scheduler never runs.
    expect(file_get_contents($supervisor.'/cron.conf'))
        ->toContain('[program:cron]')
        ->toContain('cron -f');
});

it('keeps a machine running so a delayed job fires when it is due', function () {
    // A stopped machine has no worker, so a wave scheduled for six hours out
    // waits for a passing visitor to wake the app rather than firing on time.
    expect(file_get_contents(base_path('fly.toml')))
        ->toMatch('/min_machines_running\s*=\s*[1-9]/');
});
