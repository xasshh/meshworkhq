<?php

use App\Enums\BriefStatus;
use App\Models\Brief;
use App\Models\Skill;
use App\Models\User;
use App\Notifications\BriefAlertNotification;
use App\Notifications\BriefPublishedNotification;
use App\Notifications\BriefUnlockedNotification;
use App\Notifications\HiredNotification;
use App\Notifications\NewPitchNotification;
use App\Services\BriefService;
use App\Services\UnlockService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

/**
 * Every notification the platform sends must reach the inbox, not just the
 * bell. These assert the mail channel is on, and that each meaningful event
 * actually fires one.
 */
beforeEach(function () {
    Notification::fake();
    Skill::create(['name' => 'Brand Identity', 'category' => 'Creative']);
});

test('every notification is queued and goes to both mail and in app', function (string $class) {
    $notification = new ReflectionClass($class);

    expect($notification->implementsInterface(ShouldQueue::class))->toBeTrue();
})->with([
    BriefAlertNotification::class,
    BriefPublishedNotification::class,
    BriefUnlockedNotification::class,
    HiredNotification::class,
    NewPitchNotification::class,
]);

test('publishing a brief emails the client and the matched professionals', function () {
    $client = User::factory()->client()->create();

    $matched = User::factory()->alertReady()->create([
        'skill_tags' => ['Brand Identity'],
        'bio' => 'Brand designer.',
    ]);

    $brief = Brief::factory()->for($client, 'client')->create([
        'skill_tags' => ['Brand Identity'],
        'status' => BriefStatus::Draft,
        'published_at' => null,
    ]);

    app(BriefService::class)->publish($brief);

    Notification::assertSentTo($matched, BriefAlertNotification::class);
    Notification::assertSentTo($client, BriefPublishedNotification::class);
});

test('unlocking a brief emails the client', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['credits' => 3]);
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    app(UnlockService::class)->unlock($professional, $brief);

    Notification::assertSentTo($client, BriefUnlockedNotification::class);
});

test('sending a message emails the other party', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['credits' => 3]);
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    $unlock = app(UnlockService::class)->unlock($professional, $brief);

    Livewire::actingAs($professional)
        ->test('pages::shared.conversation', ['id' => $unlock->conversation->id])
        ->set('body', 'I would like to take this on.')
        ->call('send');

    Notification::assertSentTo($client, NewPitchNotification::class);
});

test('being hired emails the professional', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['credits' => 3]);
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    app(UnlockService::class)->unlock($professional, $brief);

    app(BriefService::class)->hire($brief->refresh(), $professional);

    Notification::assertSentTo($professional, HiredNotification::class);
});

test('a brief that matches nobody still confirms to the client', function () {
    $client = User::factory()->client()->create();

    $brief = Brief::factory()->for($client, 'client')->create([
        'skill_tags' => ['Underwater Basket Weaving'],
        'status' => BriefStatus::Draft,
        'published_at' => null,
    ]);

    app(BriefService::class)->publish($brief);

    Notification::assertSentTo($client, BriefPublishedNotification::class);
});
