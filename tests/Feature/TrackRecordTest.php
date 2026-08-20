<?php

use App\Enums\BriefStatus;
use App\Models\Brief;
use App\Models\Unlock;
use App\Models\User;
use Livewire\Livewire;

test('a professional hire count reflects briefs they were hired on', function () {
    $professional = User::factory()->professional()->create();
    $other = User::factory()->professional()->create();
    $client = User::factory()->client()->create();

    Brief::factory()->count(2)->for($client, 'client')->create([
        'status' => BriefStatus::Hired,
        'hired_professional_id' => $professional->id,
    ]);

    Brief::factory()->for($client, 'client')->create([
        'status' => BriefStatus::Hired,
        'hired_professional_id' => $other->id,
    ]);

    expect($professional->hiresCount())->toBe(2)
        ->and($other->hiresCount())->toBe(1);
});

test('a client engagement count counts distinct professionals who unlocked', function () {
    $client = User::factory()->client()->create();
    $briefs = Brief::factory()->count(2)->for($client, 'client')->create();

    $first = User::factory()->professional()->create();
    $second = User::factory()->professional()->create();

    // The same professional on two briefs still counts once.
    Unlock::create(['brief_id' => $briefs[0]->id, 'professional_id' => $first->id, 'credits_spent' => 1, 'unlocked_at' => now()]);
    Unlock::create(['brief_id' => $briefs[1]->id, 'professional_id' => $first->id, 'credits_spent' => 1, 'unlocked_at' => now()]);
    Unlock::create(['brief_id' => $briefs[0]->id, 'professional_id' => $second->id, 'credits_spent' => 1, 'unlocked_at' => now()]);

    expect($client->engagementCount())->toBe(2);
});

test('a hidden track record returns null', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create(['show_hire_count' => false]);

    Brief::factory()->for($client, 'client')->create([
        'status' => BriefStatus::Hired,
        'hired_professional_id' => $professional->id,
    ]);

    expect($professional->hiresCount())->toBe(1)
        ->and($professional->publicTrackRecord())->toBeNull();

    $professional->update(['show_hire_count' => true]);

    expect($professional->fresh()->publicTrackRecord())->toBe(1);
});

test('the directory shows a hire count only when the professional allows it', function () {
    $client = User::factory()->client()->create();

    $shown = User::factory()->professional()->create([
        'name' => 'Shown Pro',
        'professional_title' => 'Designer',
        'show_hire_count' => true,
    ]);

    $hidden = User::factory()->professional()->create([
        'name' => 'Hidden Pro',
        'professional_title' => 'Designer',
        'show_hire_count' => false,
    ]);

    foreach ([$shown, $hidden] as $professional) {
        Brief::factory()->for($client, 'client')->create([
            'status' => BriefStatus::Hired,
            'hired_professional_id' => $professional->id,
        ]);
    }

    $page = Livewire::test('pages::directory');

    $page->assertSee('Shown Pro')
        ->assertSee('Hidden Pro')
        ->assertSee('Hired 1 time')
        ->assertSeeInOrder(['Shown Pro', 'Hired 1 time', 'Hidden Pro']);
});

test('a zero count is never advertised', function () {
    User::factory()->professional()->create([
        'name' => 'Fresh Pro',
        'professional_title' => 'Designer',
        'show_hire_count' => true,
    ]);

    Livewire::test('pages::directory')
        ->assertSee('Fresh Pro')
        ->assertDontSee('Hired 0 times');
});

test('a professional can switch their hire count off from their profile', function () {
    $professional = User::factory()->professional()->create(['show_hire_count' => true]);

    Livewire::actingAs($professional)
        ->test('pages::professional.profile')
        ->assertSet('showHireCount', true)
        ->set('showHireCount', false)
        ->call('save');

    expect($professional->fresh()->show_hire_count)->toBeFalse();
});

test('a client can switch their track record off from their company profile', function () {
    $client = User::factory()->client()->create(['show_engagement_count' => true]);

    Livewire::actingAs($client)
        ->test('pages::client.profile')
        ->assertSet('showEngagementCount', true)
        ->set('showEngagementCount', false)
        ->call('save');

    expect($client->fresh()->show_engagement_count)->toBeFalse();
});
