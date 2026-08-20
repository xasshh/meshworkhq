<?php

use App\Enums\BriefStatus;
use App\Models\Alert;
use App\Models\Brief;
use App\Models\Skill;
use App\Models\User;
use App\Notifications\BriefAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('client can publish a brief through the wizard and matched professionals are alerted', function () {
    Notification::fake();

    Skill::create(['name' => 'Brand Identity', 'category' => 'Creative']);

    $client = User::factory()->client()->create();

    $matched = User::factory()->professional()->count(2)->create([
        'skill_tags' => ['Brand Identity'],
        'bio' => 'Experienced brand designer',
    ]);

    $unmatched = User::factory()->professional()->create([
        'skill_tags' => ['Accounting'],
        'bio' => 'Numbers person',
    ]);

    Livewire::actingAs($client)
        ->test('pages::client.brief-wizard')
        ->set('title', 'Brand Identity for a fintech startup')
        ->set('description', 'We need a full brand identity package including logo, colors, and guidelines.')
        ->set('budgetMax', '250000')
        ->call('submitForm')
        ->assertSet('step', 'calibrating')
        ->assertSet('selectedTags', ['Brand Identity'])
        ->call('confirmTags')
        ->assertSet('step', 'live');

    $brief = Brief::first();

    expect($brief)->not->toBeNull()
        ->and($brief->status)->toBe(BriefStatus::Published)
        ->and($brief->client_id)->toBe($client->id)
        ->and($brief->skill_tags)->toBe(['Brand Identity'])
        ->and($brief->published_at)->not->toBeNull();

    expect(Alert::where('brief_id', $brief->id)->count())->toBe(2);

    Notification::assertSentTo($matched[0], BriefAlertNotification::class);
    Notification::assertSentTo($matched[1], BriefAlertNotification::class);
    Notification::assertNotSentTo($unmatched, BriefAlertNotification::class);
});

test('wizard requires at least one skill tag before publishing', function () {
    $client = User::factory()->client()->create();

    Livewire::actingAs($client)
        ->test('pages::client.brief-wizard')
        ->set('title', 'Some project title')
        ->set('description', 'A description that is long enough to pass validation rules.')
        ->set('budgetMax', '50000')
        ->call('submitForm')
        ->set('selectedTags', [])
        ->call('confirmTags')
        ->assertHasErrors(['selectedTags'])
        ->assertSet('step', 'calibrating');

    expect(Brief::count())->toBe(0);
});

test('wizard suggests tags from the skill taxonomy by keyword match', function () {
    Skill::create(['name' => 'Web Development', 'category' => 'Digital']);
    Skill::create(['name' => 'Photography', 'category' => 'Creative']);

    $client = User::factory()->client()->create();

    Livewire::actingAs($client)
        ->test('pages::client.brief-wizard')
        ->set('title', 'Web development for our store')
        ->set('description', 'Need an online shop built with checkout, no photography required for now.')
        ->set('budgetMax', '500000')
        ->call('submitForm')
        ->assertSet('selectedTags', function (array $tags) {
            return count($tags) === 2
                && in_array('Web Development', $tags)
                && in_array('Photography', $tags);
        });
});
