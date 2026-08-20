<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Not verified',
            self::Pending => 'Under review',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
        };
    }

    /** Tone for the shared pill component. */
    public function tone(): string
    {
        return match ($this) {
            self::Verified => 'live',
            self::Pending => 'warn',
            self::Rejected => 'critical',
            self::Unverified => 'muted',
        };
    }

    /** Whether a fresh submission is allowed from this state. */
    public function canSubmit(): bool
    {
        return $this === self::Unverified || $this === self::Rejected;
    }
}
