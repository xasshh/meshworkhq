<?php

use App\Enums\AlertStatus;
use App\Enums\BriefStatus;
use App\Enums\UnlockStatus;
use App\Events\BriefUnlocked;
use App\Exceptions\AlreadyUnlockedException;
use App\Exceptions\BriefNotAvailableException;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Alert;
use App\Models\Brief;
use App\Models\Unlock;
use App\Models\User;
use App\Services\UnlockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('unlock creates unlock record and deducts credit', function () {
    Event::fake([BriefUnlocked::class]);

    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['credits' => 5]);
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    $unlock = app(UnlockService::class)->unlock($professional, $brief);

    expect($unlock)->toBeInstanceOf(Unlock::class)
        ->and($unlock->status)->toBe(UnlockStatus::Active)
        ->and($professional->fresh()->credits)->toBe(4);

    Event::assertDispatched(BriefUnlocked::class);
});

test('unlock creates a conversation thread', function () {
    Event::fake([BriefUnlocked::class]);

    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['credits' => 5]);
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    $unlock = app(UnlockService::class)->unlock($professional, $brief);

    expect($unlock->fresh()->conversation)->not->toBeNull();
});

test('unlock updates alert status to unlocked', function () {
    Event::fake([BriefUnlocked::class]);

    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['credits' => 5]);
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    Alert::create([
        'brief_id' => $brief->id,
        'professional_id' => $professional->id,
        'wave' => 1,
        'status' => AlertStatus::Notified,
        'notified_at' => now(),
    ]);

    app(UnlockService::class)->unlock($professional, $brief);

    $alert = Alert::where('brief_id', $brief->id)
        ->where('professional_id', $professional->id)
        ->first();

    expect($alert->status)->toBe(AlertStatus::Unlocked);
});

test('unlock throws when brief is not available', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['credits' => 5]);
    $brief = Brief::factory()->for($client, 'client')->create(['status' => BriefStatus::Closed]);

    expect(fn () => app(UnlockService::class)->unlock($professional, $brief))
        ->toThrow(BriefNotAvailableException::class);
});

test('unlock throws when professional has no credits', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['credits' => 0]);
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    expect(fn () => app(UnlockService::class)->unlock($professional, $brief))
        ->toThrow(InsufficientCreditsException::class);
});

test('unlock throws when already unlocked', function () {
    Event::fake([BriefUnlocked::class]);

    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['credits' => 5]);
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    app(UnlockService::class)->unlock($professional, $brief);

    expect(fn () => app(UnlockService::class)->unlock($professional, $brief))
        ->toThrow(AlreadyUnlockedException::class);

    expect($professional->fresh()->credits)->toBe(4);
});
