<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Successful = 'successful';
    case Failed = 'failed';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Processing',
            self::Successful => 'Paid',
            self::Failed => 'Failed',
            self::Abandoned => 'Abandoned',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Successful => 'live',
            self::Pending => 'warn',
            default => 'critical',
        };
    }
}
