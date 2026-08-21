<?php

use App\Enums\VerificationStatus;
use App\Models\User;
use App\Notifications\VerificationApprovedNotification;
use App\Notifications\VerificationRejectedNotification;
use App\Notifications\VerificationSubmittedNotification;
use App\Services\VerificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function admin(): User
{
    $user = User::factory()->client()->create();
    $user->forceFill(['is_admin' => true])->save();

    return $user->refresh();
}

function pendingApplicant(string $type = 'cac'): User
{
    $user = User::factory()->client()->create(['company_name' => 'Okonkwo Logistics']);
    $user->forceFill([
        'verification_status' => VerificationStatus::Pending,
        'verification_type' => $type,
        'verification_reference' => $type === 'cac' ? 'RC1234567' : '4321',
        'verification_submitted_at' => now(),
    ])->save();

    return $user->refresh();
}

/**
 * These screens hand out verified badges and display identity documents, so
 * the access boundary matters more than the workflow.
 */
test('the admin area is closed to ordinary accounts', function () {
    $this->actingAs(User::factory()->client()->create())
        ->get(route('admin.verifications'))
        ->assertForbidden();

    $this->actingAs(User::factory()->professional()->create())
        ->get(route('admin.verifications'))
        ->assertForbidden();
});

test('a signed out visitor is sent to sign in, not shown a 403', function () {
    $this->get(route('admin.verifications'))->assertRedirect(route('login'));
});

test('admin access cannot be granted through a request', function () {
    $user = User::factory()->client()->create();

    // is_admin must be absent from the fillable list, or a crafted profile
    // update would be enough to hand someone the whole admin area.
    $user->fill(['is_admin' => true]);

    expect($user->is_admin)->not->toBeTrue()
        ->and($user->fresh()->isAdmin())->toBeFalse();
});

test('an admin can open the queue and see who is waiting', function () {
    $applicant = pendingApplicant();

    Livewire::actingAs(admin())
        ->test('pages::admin.verifications')
        ->assertSee($applicant->name)
        ->assertSee('RC1234567');
});

test('approving grants the badge and emails the client', function () {
    Notification::fake();
    $applicant = pendingApplicant();

    Livewire::actingAs(admin())
        ->test('pages::admin.verifications')
        ->call('open', $applicant->id)
        ->set('legalName', 'Okonkwo Logistics Ltd')
        ->call('approve')
        ->assertHasNoErrors();

    expect($applicant->fresh()->verification_status)->toBe(VerificationStatus::Verified)
        ->and($applicant->fresh()->isVerified())->toBeTrue();

    Notification::assertSentTo($applicant, VerificationApprovedNotification::class);
});

test('rejecting requires a reason and sends it on', function () {
    Notification::fake();
    $applicant = pendingApplicant();

    Livewire::actingAs(admin())
        ->test('pages::admin.verifications')
        ->call('open', $applicant->id)
        ->call('reject')
        ->assertHasErrors('reason');

    Livewire::actingAs(admin())
        ->test('pages::admin.verifications')
        ->call('open', $applicant->id)
        ->set('reason', 'The certificate did not match the RC number.')
        ->call('reject')
        ->assertHasNoErrors();

    expect($applicant->fresh()->verification_status)->toBe(VerificationStatus::Rejected)
        ->and($applicant->fresh()->verification_notes)->toContain('did not match');

    Notification::assertSentTo($applicant, VerificationRejectedNotification::class);
});

test('a submission that was already decided cannot be decided again', function () {
    $applicant = pendingApplicant();
    $applicant->forceFill(['verification_status' => VerificationStatus::Verified])->save();

    // firstOrFail is what stops a second decision landing on the same account,
    // whether from a stale tab or two reviewers working at once.
    expect(fn () => Livewire::actingAs(admin())
        ->test('pages::admin.verifications')
        ->call('open', $applicant->id)
    )->toThrow(ModelNotFoundException::class);
});

test('submitting a verification tells the reviewers', function () {
    Notification::fake();
    Storage::fake('local');

    $reviewer = admin();
    $client = User::factory()->client()->create(['company_name' => 'Okonkwo Logistics']);

    app(VerificationService::class)->submitCac(
        $client,
        'RC1234567',
        UploadedFile::fake()->create('cac.pdf', 40, 'application/pdf'),
    );

    Notification::assertSentTo($reviewer, VerificationSubmittedNotification::class);
});

/**
 * A CAC certificate is an identity document. It lives on the private disk and
 * the only route to it must be an authenticated admin request.
 */
test('a certificate is never readable by anyone but an admin', function () {
    Storage::fake('local');

    $client = User::factory()->client()->create(['company_name' => 'Okonkwo Logistics']);
    app(VerificationService::class)->submitCac(
        $client,
        'RC1234567',
        UploadedFile::fake()->create('cac.pdf', 40, 'application/pdf'),
    );

    $url = route('admin.verifications.document', ['user' => $client->id]);

    $this->get($url)->assertRedirect();
    $this->actingAs(User::factory()->client()->create())->get($url)->assertForbidden();
    $this->actingAs(User::factory()->professional()->create())->get($url)->assertForbidden();

    // Not even the applicant may fetch it back through this route.
    $this->actingAs($client)->get($url)->assertForbidden();

    $this->actingAs(admin())->get($url)->assertOk();
});

test('the certificate is gone once a decision is recorded', function () {
    Storage::fake('local');

    $client = User::factory()->client()->create(['company_name' => 'Okonkwo Logistics']);
    app(VerificationService::class)->submitCac(
        $client,
        'RC1234567',
        UploadedFile::fake()->create('cac.pdf', 40, 'application/pdf'),
    );

    $path = $client->fresh()->verification_document_path;
    Storage::disk('local')->assertExists($path);

    app(VerificationService::class)->approve($client->fresh(), 'Okonkwo Logistics Ltd');

    Storage::disk('local')->assertMissing($path);

    $this->actingAs(admin())
        ->get(route('admin.verifications.document', ['user' => $client->id]))
        ->assertNotFound();
});
