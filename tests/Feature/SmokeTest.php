<?php

use App\Models\Brief;
use App\Models\Conversation;
use App\Models\User;

test('guest pages render', function (string $uri) {
    $this->get($uri)->assertOk();
})->with([
    '/',
    '/professionals',
    '/how-it-works',
    '/for-talent',
    '/professional/register',
    '/professional/login',
    '/client/register',
    '/client/login',
    '/login',
    '/register',
    '/forgot-password',
]);

test('a professional profile page renders for a guest', function () {
    $professional = User::factory()->professional()->create(['professional_title' => 'Designer']);

    $this->get(route('professionals.show', ['id' => $professional->id]))->assertOk();
});

test('professional pages render', function (string $route) {
    $professional = User::factory()->professional()->create();

    $this->actingAs($professional)->get(route($route))->assertOk();
})->with([
    'professional.dashboard',
    'professional.alerts',
    'professional.pitches',
    'professional.messages',
    'professional.profile',
    'professional.wallet',
    'profile.edit',
    'appearance.edit',
]);

test('client pages render', function (string $route) {
    $client = User::factory()->client()->create();

    $this->actingAs($client)->get(route($route))->assertOk();
})->with([
    'client.dashboard',
    'client.brief.create',
    'client.briefs',
    'client.messages',
    'client.profile',
    'client.verification',
]);

test('brief detail pages render for both roles', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create();
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    $this->actingAs($client)
        ->get(route('client.brief.detail', ['ulid' => $brief->ulid]))
        ->assertOk();

    $this->actingAs($professional)
        ->get(route('professional.brief.detail', ['ulid' => $brief->ulid]))
        ->assertOk();
});

test('conversation page renders for both participants', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create();
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    $conversation = Conversation::create([
        'brief_id' => $brief->id,
        'client_id' => $client->id,
        'professional_id' => $professional->id,
    ]);

    $this->actingAs($client)
        ->get(route('client.conversation', ['id' => $conversation->id]))
        ->assertOk();

    $this->actingAs($professional)
        ->get(route('professional.conversation', ['id' => $conversation->id]))
        ->assertOk();
});

test('role middleware blocks cross-role access', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create();

    $this->actingAs($client)->get(route('professional.dashboard'))->assertForbidden();
    $this->actingAs($professional)->get(route('client.dashboard'))->assertForbidden();

    // The admin area shows identity documents, so neither role reaches it.
    $this->actingAs($client)->get(route('admin.verifications'))->assertForbidden();
    $this->actingAs($professional)->get(route('admin.verifications'))->assertForbidden();
});

test('the admin verification queue renders for staff', function () {
    $admin = User::factory()->client()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin->refresh())->get(route('admin.verifications'))->assertOk();
});
