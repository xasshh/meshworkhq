<?php

use App\Enums\BriefStatus;
use App\Events\BriefPublished;
use App\Exceptions\BriefNotAvailableException;
use App\Models\Brief;
use App\Models\User;
use App\Services\BriefService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('publish transitions draft brief and fires event', function () {
    Event::fake([BriefPublished::class]);

    $client = User::factory()->client()->create();
    $brief = Brief::factory()->for($client, 'client')->create(['status' => BriefStatus::Draft]);

    app(BriefService::class)->publish($brief);

    expect($brief->fresh()->status)->toBe(BriefStatus::Published)
        ->and($brief->fresh()->published_at)->not->toBeNull()
        ->and($brief->fresh()->expires_at)->not->toBeNull();

    Event::assertDispatched(BriefPublished::class);
});

test('publish throws when brief is not draft', function () {
    $client = User::factory()->client()->create();
    $brief = Brief::factory()->for($client, 'client')->create(['status' => BriefStatus::Published]);

    expect(fn () => app(BriefService::class)->publish($brief))
        ->toThrow(BriefNotAvailableException::class);
});

test('close transitions active brief', function () {
    $client = User::factory()->client()->create();
    $brief = Brief::factory()->for($client, 'client')->create(['status' => BriefStatus::Published]);

    app(BriefService::class)->close($brief);

    expect($brief->fresh()->status)->toBe(BriefStatus::Closed);
});

test('close throws when brief is not active', function () {
    $client = User::factory()->client()->create();
    $brief = Brief::factory()->for($client, 'client')->create(['status' => BriefStatus::Closed]);

    expect(fn () => app(BriefService::class)->close($brief))
        ->toThrow(BriefNotAvailableException::class);
});

test('hire sets hired professional and transitions status', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create();
    $brief = Brief::factory()->for($client, 'client')->create(['status' => BriefStatus::ReceivingPitches]);

    app(BriefService::class)->hire($brief, $professional);

    expect($brief->fresh()->status)->toBe(BriefStatus::Hired)
        ->and($brief->fresh()->hired_professional_id)->toBe($professional->id);
});

test('expire overdue marks active briefs as expired', function () {
    $client = User::factory()->client()->create();
    $brief = Brief::factory()->for($client, 'client')->create([
        'status' => BriefStatus::Published,
        'expires_at' => now()->subDay(),
    ]);

    $count = app(BriefService::class)->expireOverdue();

    expect($count)->toBe(1)
        ->and($brief->fresh()->status)->toBe(BriefStatus::Expired);
});
