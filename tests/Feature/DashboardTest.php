<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated client users are redirected to the client dashboard', function () {
    $user = User::factory()->client()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('client.dashboard'));
});

test('authenticated professional users are redirected to the professional dashboard', function () {
    $user = User::factory()->professional()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('professional.dashboard'));
});
