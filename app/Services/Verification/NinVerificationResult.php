<?php

namespace App\Services\Verification;

/**
 * The outcome of a NIN lookup, stripped of the number itself.
 *
 * A verifier returns this and nothing else, so no caller can accidentally
 * persist a full NIN: the raw value never leaves the verifier.
 */
final readonly class NinVerificationResult
{
    public function __construct(
        public bool $verified,
        public ?string $legalName = null,
        public ?string $lastFour = null,
        public ?string $failureReason = null,
        /** True when no automated decision was reached and a human must look. */
        public bool $needsManualReview = false,
    ) {}

    public static function verified(string $legalName, string $lastFour): self
    {
        return new self(verified: true, legalName: $legalName, lastFour: $lastFour);
    }

    public static function rejected(string $reason, ?string $lastFour = null): self
    {
        return new self(verified: false, lastFour: $lastFour, failureReason: $reason);
    }

    public static function manualReview(?string $lastFour = null): self
    {
        return new self(verified: false, lastFour: $lastFour, needsManualReview: true);
    }
}
