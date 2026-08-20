<?php

use App\Enums\AlertStatus;
use App\Models\Alert;
use App\Models\Brief;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\BriefAlertNotification;
use App\Notifications\NewPitchNotification;

function renderAlertMail(): string
{
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create();
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    $alert = Alert::create([
        'brief_id' => $brief->id,
        'professional_id' => $professional->id,
        'wave' => 1,
        'status' => AlertStatus::Notified,
        'notified_at' => now(),
    ]);

    return (string) (new BriefAlertNotification($alert->load('brief')))->toMail($professional)->render();
}

test('emails carry the logo when it is on a reachable host', function () {
    config(['mail.logo_url' => 'https://meshworkhq.com/images/meshwork-lockup.png']);

    $html = renderAlertMail();

    expect($html)->toContain('https://meshworkhq.com/images/meshwork-lockup.png')
        ->and($html)->toContain('class="logo"');
});

test('emails fall back to the wordmark when the logo host is not reachable', function (string $url) {
    config(['mail.logo_url' => $url]);

    $html = renderAlertMail();

    // A broken image in every inbox is worse than plain text.
    expect($html)->not->toContain('class="logo"')
        ->and($html)->toContain(config('app.name'));
})->with([
    'localhost' => ['http://localhost/images/meshwork-lockup.png'],
    'loopback ip' => ['http://127.0.0.1:8000/images/meshwork-lockup.png'],
    'localhost with port' => ['http://localhost:8000/images/meshwork-lockup.png'],
    'empty' => [''],
]);

test('the call to action uses the brand blue', function () {
    config(['mail.logo_url' => 'https://meshworkhq.com/images/meshwork-lockup.png']);

    expect(renderAlertMail())->toContain('#3F5F80');
});

test('message notifications carry the same branding', function () {
    config(['mail.logo_url' => 'https://meshworkhq.com/images/meshwork-lockup.png']);

    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create();
    $brief = Brief::factory()->published()->for($client, 'client')->create();

    $conversation = Conversation::create([
        'brief_id' => $brief->id,
        'client_id' => $client->id,
        'professional_id' => $professional->id,
    ]);

    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $professional->id,
        'body' => 'I would like to take this on.',
    ]);

    $html = (string) (new NewPitchNotification($message))->toMail($client)->render();

    expect($html)->toContain('class="logo"')
        ->and($html)->toContain('meshwork-lockup.png');
});

test('no email promises a refund we do not offer', function () {
    config(['mail.logo_url' => 'https://meshworkhq.com/images/meshwork-lockup.png']);

    $html = renderAlertMail();

    expect($html)->not->toContain('14 days')
        ->not->toContain('comes back to you')
        ->not->toContain('Refunded');
});
