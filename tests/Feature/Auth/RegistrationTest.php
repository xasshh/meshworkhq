<?php

use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
    Notification::fake();
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('client registration sends a verification email and holds them at the notice', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Jane Client',
        'email' => 'client@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'client',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('verification.notice', absolute: false));

    $this->assertAuthenticated();

    $user = User::where('email', 'client@example.com')->first();
    expect($user->role->value)->toBe('client')
        ->and($user->credits)->toBe(0)
        ->and($user->hasVerifiedEmail())->toBeFalse();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('professional registration sends a verification email and holds them at the notice', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Pro',
        'email' => 'pro@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'professional',
        'professional_title' => 'Brand Designer',
        'skill_tags' => json_encode(['Branding', 'UI/UX Design']),
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('verification.notice', absolute: false));

    $this->assertAuthenticated();

    Notification::assertSentTo(
        User::where('email', 'pro@example.com')->first(),
        VerifyEmail::class,
    );

    $user = User::where('email', 'pro@example.com')->first();
    expect($user->role->value)->toBe('professional')
        ->and($user->credits)->toBe(3)
        ->and($user->skill_tags)->toBe(['Branding', 'UI/UX Design'])
        ->and($user->professional_title)->toBe('Brand Designer');

    // Welcome bonus must be recorded in the credit ledger.
    expect(CreditTransaction::where('user_id', $user->id)->count())->toBe(1);
    $tx = CreditTransaction::where('user_id', $user->id)->first();
    expect($tx->amount)->toBe(3)
        ->and($tx->balance_after)->toBe(3)
        ->and($tx->expires_at)->not->toBeNull();
});

test('professional registration fails without selecting any skills', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'No Skills Pro',
        'email' => 'noskills@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'professional',
        'professional_title' => 'Consultant',
        'skill_tags' => json_encode([]),   // empty array
    ]);

    $response->assertSessionHasErrors('skill_tags');
    $this->assertGuest();
});

test('professional registration fails with missing skill tags', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Missing Skills Pro',
        'email' => 'missing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'professional',
        'professional_title' => 'Consultant',
        // skill_tags field absent entirely
    ]);

    $response->assertSessionHasErrors('skill_tags');
    $this->assertGuest();
});
