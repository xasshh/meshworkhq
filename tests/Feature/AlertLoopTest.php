<?php

use App\Enums\AlertStatus;
use App\Enums\BriefStatus;
use App\Models\Alert;
use App\Models\Brief;
use App\Models\Skill;
use App\Models\User;
use App\Services\BriefService;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

/**
 * End to end cover for the loop the whole product rests on: a client publishes
 * a brief, and a professional whose skills match sees it on their own screens.
 * The assertions deliberately go through the UI rather than the alerts table,
 * because a row nobody can see is not an alert.
 */
beforeEach(function () {
    Notification::fake();

    Skill::create(['name' => 'Brand Identity', 'category' => 'Creative']);
    Skill::create(['name' => 'Accounting', 'category' => 'Finance']);
});

function publishBriefFor(User $client, array $skillTags = ['Brand Identity']): Brief
{
    $brief = Brief::factory()->for($client, 'client')->create([
        'title' => 'Full brand identity for a Lekki logistics company',
        'description' => 'Logo, colour system, typography and a short set of guidelines.',
        'skill_tags' => $skillTags,
        'budget_min' => 350_000,
        'budget_max' => 500_000,
        'status' => BriefStatus::Draft,
        'published_at' => null,
    ]);

    app(BriefService::class)->publish($brief);

    return $brief->refresh();
}

test('a matched professional sees the brief in their alert feed', function () {
    $client = User::factory()->client()->create();

    $professional = User::factory()->alertReady()->create([
        'skill_tags' => ['Brand Identity', 'Logo Design'],
        'bio' => 'Brand designer of ten years.',
    ]);

    $brief = publishBriefFor($client);

    // The alert reached them.
    expect(Alert::where('brief_id', $brief->id)->where('professional_id', $professional->id)->exists())
        ->toBeTrue();

    // And it is actually on screen.
    Livewire::actingAs($professional)
        ->test('pages::professional.alert-feed')
        ->assertSee('Full brand identity for a Lekki logistics company')
        ->assertSee('Brand Identity');
});

test('an unmatched professional does not see the brief', function () {
    $client = User::factory()->client()->create();

    $unmatched = User::factory()->alertReady()->create([
        'skill_tags' => ['Accounting'],
        'bio' => 'Numbers person.',
    ]);

    publishBriefFor($client);

    Livewire::actingAs($unmatched)
        ->test('pages::professional.alert-feed')
        ->assertDontSee('Full brand identity for a Lekki logistics company')
        ->assertSee('No alerts yet');
});

test('the matched brief also surfaces on the professional overview', function () {
    $client = User::factory()->client()->create();

    $professional = User::factory()->alertReady()->create([
        'skill_tags' => ['Brand Identity'],
        'bio' => 'Brand designer.',
    ]);

    publishBriefFor($client);

    Livewire::actingAs($professional)
        ->test('pages::professional.dashboard')
        ->assertSee('Full brand identity for a Lekki logistics company');
});

test('the sidebar badge counts the unread alert', function () {
    $client = User::factory()->client()->create();

    $professional = User::factory()->alertReady()->create([
        'skill_tags' => ['Brand Identity'],
        'bio' => 'Brand designer.',
    ]);

    publishBriefFor($client);

    expect(Alert::where('professional_id', $professional->id)->unread()->count())->toBe(1);

    // Opening the feed marks it viewed, so the badge clears.
    $alert = Alert::where('professional_id', $professional->id)->firstOrFail();

    Livewire::actingAs($professional)
        ->test('pages::professional.alert-feed')
        ->call('markViewed', $alert->id);

    expect($alert->refresh()->status)->toBe(AlertStatus::Viewed)
        ->and(Alert::where('professional_id', $professional->id)->unread()->count())->toBe(0);
});

test('a professional can unlock a matched brief and land in a conversation', function () {
    $client = User::factory()->client()->create();

    $professional = User::factory()->alertReady()->create([
        'skill_tags' => ['Brand Identity'],
        'bio' => 'Brand designer.',
        'credits' => 3,
    ]);

    $brief = publishBriefFor($client);
    $alert = Alert::where('professional_id', $professional->id)->firstOrFail();

    Livewire::actingAs($professional)
        ->test('pages::professional.alert-feed')
        ->call('unlock', $alert->id)
        ->assertRedirect();

    expect($professional->refresh()->credits)->toBe(2)
        ->and($alert->refresh()->status)->toBe(AlertStatus::Unlocked)
        ->and($brief->refresh()->total_unlocks)->toBe(1);

    // The client now sees the pitch on their own brief.
    Livewire::actingAs($client)
        ->test('pages::client.brief-detail', ['ulid' => $brief->ulid])
        ->set('tab', 'responses')
        ->assertSee($professional->name);
});

test('a professional with no skill tags is never matched', function () {
    $client = User::factory()->client()->create();

    $professional = User::factory()->alertReady()->create([
        'skill_tags' => null,
        'bio' => 'I have not tagged any skills.',
    ]);

    $brief = publishBriefFor($client);

    expect(Alert::where('brief_id', $brief->id)->where('professional_id', $professional->id)->exists())
        ->toBeFalse();
});

test('a draft brief alerts nobody until it is published', function () {
    $client = User::factory()->client()->create();

    User::factory()->alertReady()->create([
        'skill_tags' => ['Brand Identity'],
        'bio' => 'Brand designer.',
    ]);

    $brief = Brief::factory()->for($client, 'client')->create([
        'skill_tags' => ['Brand Identity'],
        'status' => BriefStatus::Draft,
        'published_at' => null,
    ]);

    expect(Alert::where('brief_id', $brief->id)->count())->toBe(0);
});
