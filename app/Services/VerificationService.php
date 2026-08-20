<?php

namespace App\Services;

use App\Enums\VerificationStatus;
use App\Exceptions\VerificationNotAllowedException;
use App\Models\User;
use App\Services\Verification\NinVerifier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class VerificationService
{
    /** Verification documents live on the private disk, never the public one. */
    private const DISK = 'local';

    public function __construct(
        private readonly NinVerifier $ninVerifier,
    ) {}

    /**
     * Individual verification by National Identification Number.
     *
     * The raw NIN is handed to the verifier and then goes out of scope. It is
     * never written to the database, the log, or the session.
     *
     * @throws VerificationNotAllowedException
     */
    public function submitNin(User $user, string $nin): VerificationStatus
    {
        $this->guardCanSubmit($user);

        $result = $this->ninVerifier->verify($nin, $user->name);

        $status = match (true) {
            $result->verified => VerificationStatus::Verified,
            $result->needsManualReview => VerificationStatus::Pending,
            default => VerificationStatus::Rejected,
        };

        $user->forceFill([
            'verification_status' => $status,
            'verification_type' => 'nin',
            'verification_reference' => $result->lastFour,
            'verification_legal_name' => $result->legalName,
            'verification_submitted_at' => now(),
            'verification_reviewed_at' => $status === VerificationStatus::Pending ? null : now(),
            'verification_notes' => $result->failureReason,
        ])->save();

        return $status;
    }

    /**
     * Company verification by CAC registration number plus certificate.
     *
     * Always lands in manual review: the RC number is checkable against the
     * public CAC register, and a human confirms the certificate matches it.
     *
     * @throws VerificationNotAllowedException
     */
    public function submitCac(User $user, string $rcNumber, UploadedFile $certificate): VerificationStatus
    {
        $this->guardCanSubmit($user);

        $previous = $user->verification_document_path;

        $path = $certificate->store('verification', self::DISK);

        $user->forceFill([
            'verification_status' => VerificationStatus::Pending,
            'verification_type' => 'cac',
            'verification_reference' => strtoupper(trim($rcNumber)),
            'verification_document_path' => $path,
            'verification_submitted_at' => now(),
            'verification_reviewed_at' => null,
            'verification_notes' => null,
        ])->save();

        if ($previous) {
            Storage::disk(self::DISK)->delete($previous);
        }

        return VerificationStatus::Pending;
    }

    /**
     * Approve a pending submission. The certificate is deleted on approval:
     * once the decision is recorded there is no reason to keep holding someone
     * incorporation document.
     */
    public function approve(User $user, ?string $legalName = null): void
    {
        $document = $user->verification_document_path;

        $user->forceFill([
            'verification_status' => VerificationStatus::Verified,
            'verification_legal_name' => $legalName ?? $user->verification_legal_name,
            'verification_document_path' => null,
            'verification_reviewed_at' => now(),
            'verification_notes' => null,
        ])->save();

        if ($document) {
            Storage::disk(self::DISK)->delete($document);
        }
    }

    public function reject(User $user, string $reason): void
    {
        $document = $user->verification_document_path;

        $user->forceFill([
            'verification_status' => VerificationStatus::Rejected,
            'verification_document_path' => null,
            'verification_reviewed_at' => now(),
            'verification_notes' => $reason,
        ])->save();

        if ($document) {
            Storage::disk(self::DISK)->delete($document);
        }
    }

    /** @throws VerificationNotAllowedException */
    private function guardCanSubmit(User $user): void
    {
        if (! $user->verification_status->canSubmit()) {
            throw new VerificationNotAllowedException(
                $user->verification_status === VerificationStatus::Verified
                    ? 'This account is already verified.'
                    : 'A verification submission is already under review.'
            );
        }
    }
}
