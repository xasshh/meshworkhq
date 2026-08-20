<?php

use App\Enums\BriefStatus;
use App\Models\Alert;
use App\Models\Brief;
use App\Models\User;
use App\Notifications\BriefAlertNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(fn () => Notification::fake());

test('anyone can view a professional profile', function () {
    $professional = User::factory()->professional()->create([
        'name' => 'Emeka Nwosu',
        'professional_title' => 'Brand designer',
        'bio' => 'I build brand systems.',
        'skill_tags' => ['Brand Identity'],
    ]);

    $this->get(route('professionals.show', ['id' => $professional->id]))
        ->assertOk()
        ->assertSee('Emeka Nwosu')
        ->assertSee('Brand designer')
        ->assertSee('I build brand systems.')
        ->assertSee('Brand Identity');
});

test('a client profile is not reachable through the professional directory', function () {
    $client = User::factory()->client()->create();

    $this->get(route('professionals.show', ['id' => $client->id]))
        ->assertNotFound();
});

test('a guest is invited to post a brief rather than message directly', function () {
    $professional = User::factory()->professional()->create(['professional_title' => 'Designer']);

    $this->get(route('professionals.show', ['id' => $professional->id]))
        ->assertSee('Post a brief');
});

test('a client can alert a professional to one of their live briefs', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['professional_title' => 'Designer']);

    $brief = Brief::factory()->published()->for($client, 'client')->create([
        'title' => 'Brand identity for a logistics company',
    ]);

    Livewire::actingAs($client)
        ->test('pages::professional-profile', ['id' => $professional->id])
        ->assertSet('briefId', $brief->id)
        ->call('invite');

    expect(Alert::where('brief_id', $brief->id)->where('professional_id', $professional->id)->exists())
        ->toBeTrue()
        ->and($brief->refresh()->total_alerts_sent)->toBe(1);

    Notification::assertSentTo($professional, BriefAlertNotification::class);
});

test('inviting the same professional twice does not alert them twice', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['professional_title' => 'Designer']);
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    $component = Livewire::actingAs($client)
        ->test('pages::professional-profile', ['id' => $professional->id]);

    $component->call('invite');
    $component->call('invite');

    expect(Alert::where('brief_id', $brief->id)->where('professional_id', $professional->id)->count())->toBe(1);

    Notification::assertSentToTimes($professional, BriefAlertNotification::class, 1);
});

test('a client with no live brief is pointed at posting one', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['professional_title' => 'Designer']);

    Livewire::actingAs($client)
        ->test('pages::professional-profile', ['id' => $professional->id])
        ->assertSet('briefId', null)
        ->assertSee('You have no live briefs');
});

test('a closed brief cannot be used to alert anyone', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['professional_title' => 'Designer']);

    Brief::factory()->published()->for($client, 'client')->create();
    $closed = Brief::factory()->for($client, 'client')->create([
        'status' => BriefStatus::Closed,
    ]);

    // The guard reports the problem rather than throwing at the user.
    Livewire::actingAs($client)
        ->test('pages::professional-profile', ['id' => $professional->id])
        ->set('briefId', $closed->id)
        ->call('invite');

    expect(Alert::where('brief_id', $closed->id)->count())->toBe(0);

    Notification::assertNothingSent();
});

test('a client cannot alert someone using another client brief', function () {
    $client = User::factory()->client()->create();
    $stranger = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['professional_title' => 'Designer']);

    $notMine = Brief::factory()->published()->for($stranger, 'client')->create();

    Livewire::actingAs($client)
        ->test('pages::professional-profile', ['id' => $professional->id])
        ->set('briefId', $notMine->id)
        ->call('invite');
})->throws(ModelNotFoundException::class);

test('the directory links each card through to the profile', function () {
    $professional = User::factory()->professional()->create([
        'name' => 'Adaeze Okoye',
        'professional_title' => 'Designer',
    ]);

    $this->get(route('directory'))
        ->assertOk()
        ->assertSee(route('professionals.show', ['id' => $professional->id]), escape: false);
});
