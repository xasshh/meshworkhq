<?php

use App\Enums\AlertStatus;
use App\Enums\BriefStatus;
use App\Models\Alert;
use App\Models\Brief;
use App\Models\User;
use App\Services\AlertService;
use App\Services\MatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('dispatch wave creates alerts and notifies professionals', function () {
    Queue::fake();

    $client = User::factory()->client()->create();
    $brief = Brief::factory()->published()->for($client, 'client')->create([
        'skill_tags' => ['Branding'],
    ]);

    $professionals = User::factory()->professional()->count(3)->create([
        'skill_tags' => ['Branding'],
        'bio' => 'Experienced designer',
    ]);

    app(AlertService::class)->dispatchWave($brief, $professionals, 1);

    expect(Alert::where('brief_id', $brief->id)->count())->toBe(3)
        ->and($brief->fresh()->total_alerts_sent)->toBe(3)
        ->and($brief->fresh()->alert_wave)->toBe(1);
});

test('dispatch wave skips professionals over daily spam cap', function () {
    Queue::fake();

    $client = User::factory()->client()->create();
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    $professional = User::factory()->professional()->create(['bio' => 'Designer']);

    // Simulate 20 existing alerts today for this professional.
    $otherClient = User::factory()->client()->create();
    $otherBriefs = Brief::factory()->published()->for($otherClient, 'client')->count(20)->create();
    foreach ($otherBriefs as $otherBrief) {
        Alert::create([
            'brief_id' => $otherBrief->id,
            'professional_id' => $professional->id,
            'wave' => 1,
            'status' => AlertStatus::Notified,
            'notified_at' => now()->subMinutes(10),
        ]);
    }

    app(AlertService::class)->dispatchWave($brief, collect([$professional]), 1);

    expect(Alert::where('brief_id', $brief->id)->where('professional_id', $professional->id)->count())->toBe(0);
});

test('dispatch wave skips when brief is no longer active', function () {
    Queue::fake();

    $client = User::factory()->client()->create();
    $brief = Brief::factory()->for($client, 'client')->create(['status' => BriefStatus::Closed]);
    $professional = User::factory()->professional()->create(['bio' => 'Designer']);

    app(AlertService::class)->dispatchWave($brief, collect([$professional]), 1);

    expect(Alert::where('brief_id', $brief->id)->count())->toBe(0);
});

test('matching service finds candidates with skill overlap', function () {
    $client = User::factory()->client()->create();
    $brief = Brief::factory()->published()->for($client, 'client')->create([
        'skill_tags' => ['SEO', 'Copywriting'],
    ]);

    $matched = User::factory()->professional()->count(2)->create([
        'skill_tags' => ['SEO', 'Photography'],
        'bio' => 'SEO specialist',
    ]);

    $unmatched = User::factory()->professional()->count(2)->create([
        'skill_tags' => ['Photography', 'Videography'],
        'bio' => 'Photographer',
    ]);

    $candidates = app(MatchingService::class)->findCandidates($brief);

    expect($candidates->count())->toBe(2)
        ->and($candidates->pluck('id')->toArray())
        ->toContain($matched->first()->id);
});

test('split into waves returns correct sizes', function () {
    $users = User::factory()->professional()->count(50)->create(['bio' => 'Pro']);
    $waves = app(MatchingService::class)->splitIntoWaves($users);

    expect($waves[1]->count())->toBe(10)
        ->and($waves[2]->count())->toBe(15)
        ->and($waves[3]->count())->toBe(25);
});
