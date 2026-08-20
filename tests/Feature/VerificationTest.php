<?php

use App\Enums\VerificationStatus;
use App\Exceptions\VerificationNotAllowedException;
use App\Models\Brief;
use App\Models\User;
use App\Notifications\VerificationApprovedNotification;
use App\Notifications\VerificationRejectedNotification;
use App\Services\Verification\NinVerificationResult;
use App\Services\Verification\NinVerifier;
use App\Services\VerificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('a new account starts unverified', function () {
    $client = User::factory()->client()->create();

    expect($client->verification_status)->toBe(VerificationStatus::Unverified)
        ->and($client->isVerified())->toBeFalse();
});

test('a nin submission never stores the full number', function () {
    $client = User::factory()->client()->create(['name' => 'Adaeze Okonkwo']);

    app()->bind(NinVerifier::class, fn () => new class implements NinVerifier
    {
        public function verify(string $nin, string $expectedName): NinVerificationResult
        {
            return NinVerificationResult::verified($expectedName, substr($nin, -4));
        }
    });

    app(VerificationService::class)->submitNin($client, '12345678901');

    $client->refresh();

    expect($client->verification_status)->toBe(VerificationStatus::Verified)
        ->and($client->verification_reference)->toBe('8901')
        ->and($client->isVerified())->toBeTrue();

    // The raw number appears nowhere on the record.
    expect(collect($client->getAttributes())->filter(
        fn ($value) => is_string($value) && str_contains($value, '12345678901')
    ))->toBeEmpty();
});

test('the default verifier sends a nin to manual review rather than guessing', function () {
    $client = User::factory()->client()->create();

    $status = app(VerificationService::class)->submitNin($client, '12345678901');

    expect($status)->toBe(VerificationStatus::Pending)
        ->and($client->refresh()->verification_reference)->toBe('8901')
        ->and($client->isVerified())->toBeFalse();
});

test('a rejected nin records the reason and can be resubmitted', function () {
    $client = User::factory()->client()->create();

    app()->bind(NinVerifier::class, fn () => new class implements NinVerifier
    {
        public function verify(string $nin, string $expectedName): NinVerificationResult
        {
            return NinVerificationResult::rejected('The name did not match.', substr($nin, -4));
        }
    });

    app(VerificationService::class)->submitNin($client, '12345678901');
    $client->refresh();

    expect($client->verification_status)->toBe(VerificationStatus::Rejected)
        ->and($client->verification_notes)->toBe('The name did not match.')
        ->and($client->verification_status->canSubmit())->toBeTrue();
});

test('a cac submission stores the certificate on the private disk', function () {
    Storage::fake('local');

    $client = User::factory()->client()->create();

    $status = app(VerificationService::class)->submitCac(
        $client,
        'rc1234567',
        UploadedFile::fake()->create('certificate.pdf', 200, 'application/pdf'),
    );

    $client->refresh();

    expect($status)->toBe(VerificationStatus::Pending)
        ->and($client->verification_reference)->toBe('RC1234567')
        ->and($client->verification_document_path)->not->toBeNull();

    Storage::disk('local')->assertExists($client->verification_document_path);
});

test('approving deletes the stored document', function () {
    Storage::fake('local');

    $client = User::factory()->client()->create();
    $service = app(VerificationService::class);

    $service->submitCac($client, 'RC1234567', UploadedFile::fake()->create('certificate.pdf', 200, 'application/pdf'));
    $path = $client->refresh()->verification_document_path;

    $service->approve($client, 'Swiftlane Logistics Limited');
    $client->refresh();

    expect($client->verification_status)->toBe(VerificationStatus::Verified)
        ->and($client->verification_legal_name)->toBe('Swiftlane Logistics Limited')
        ->and($client->verification_document_path)->toBeNull();

    Storage::disk('local')->assertMissing($path);
});

test('a verified account cannot submit again', function () {
    $client = User::factory()->client()->create(['verification_status' => VerificationStatus::Verified]);

    app(VerificationService::class)->submitNin($client, '12345678901');
})->throws(VerificationNotAllowedException::class);

test('a pending account cannot submit again', function () {
    $client = User::factory()->client()->create(['verification_status' => VerificationStatus::Pending]);

    app(VerificationService::class)->submitNin($client, '12345678901');
})->throws(VerificationNotAllowedException::class);

test('the verification page rejects a malformed nin', function () {
    $client = User::factory()->client()->create();

    Livewire::actingAs($client)
        ->test('pages::client.verification')
        ->set('method', 'nin')
        ->set('nin', '123')
        ->call('submit')
        ->assertHasErrors(['nin']);

    expect($client->refresh()->verification_status)->toBe(VerificationStatus::Unverified);
});

test('the verification page clears the nin from component state after submitting', function () {
    $client = User::factory()->client()->create();

    Livewire::actingAs($client)
        ->test('pages::client.verification')
        ->set('method', 'nin')
        ->set('nin', '12345678901')
        ->call('submit')
        ->assertSet('nin', '');
});

test('a verified client badge shows to the professional on the brief', function () {
    $client = User::factory()->client()->create([
        'name' => 'Adaeze Okonkwo',
        'verification_status' => VerificationStatus::Verified,
    ]);

    $professional = User::factory()->professional()->create(['skill_tags' => ['Brand Identity']]);

    $brief = Brief::factory()->published()->for($client, 'client')->create([
        'skill_tags' => ['Brand Identity'],
    ]);

    Livewire::actingAs($professional)
        ->test('pages::professional.brief-detail', ['ulid' => $brief->ulid])
        ->assertSee('Verified');
});

test('an unverified client shows no badge', function () {
    $client = User::factory()->client()->create();
    $professional = User::factory()->professional()->create();

    $brief = Brief::factory()->published()->for($client, 'client')->create();

    Livewire::actingAs($professional)
        ->test('pages::professional.brief-detail', ['ulid' => $brief->ulid])
        ->assertDontSee('Verified');
});

/**
 * The verification page tells a client they will hear back either way. A
 * decision that reaches nobody makes that a lie, and leaves them reloading the
 * page to find out whether anything happened.
 */
test('an approval reaches the client', function () {
    Notification::fake();

    $client = User::factory()->client()->create();
    $client->forceFill(['verification_status' => VerificationStatus::Pending])->save();

    app(VerificationService::class)->approve($client, 'Adaeze Okonkwo');

    Notification::assertSentTo($client, VerificationApprovedNotification::class);
});

test('a rejection reaches the client and carries the reason', function () {
    Notification::fake();

    $client = User::factory()->client()->create();
    $client->forceFill(['verification_status' => VerificationStatus::Pending])->save();

    app(VerificationService::class)->reject($client, 'The name did not match the certificate.');

    Notification::assertSentTo(
        $client,
        VerificationRejectedNotification::class,
        function (VerificationRejectedNotification $notification) use ($client) {
            $mail = $notification->toMail($client);

            // Without the reason a rejection leaves someone guessing what to fix.
            return collect($mail->introLines)->contains(
                fn (string $line): bool => str_contains($line, 'The name did not match the certificate.')
            );
        }
    );
});
