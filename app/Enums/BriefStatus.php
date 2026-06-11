<?php

namespace App\Enums;

enum BriefStatus: string
{
    case Draft = 'draft';
    case AiReview = 'ai_review';
    case Published = 'published';
    case ReceivingPitches = 'receiving_pitches';
    case Shortlisting = 'shortlisting';
    case Hired = 'hired';
    case Closed = 'closed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::AiReview => 'Under Review',
            self::Published => 'Published',
            self::ReceivingPitches => 'Receiving Pitches',
            self::Shortlisting => 'Shortlisting',
            self::Hired => 'Hired',
            self::Closed => 'Closed',
            self::Expired => 'Expired',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Published, self::ReceivingPitches, self::Shortlisting]);
    }

    public function canReceivePitches(): bool
    {
        return in_array($this, [self::Published, self::ReceivingPitches]);
    }
}
