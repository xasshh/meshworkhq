<?php

namespace App\Enums;

enum CreditTransactionType: string
{
    case Purchase = 'purchase';
    case Spend = 'spend';
    case Refund = 'refund';
    case Bonus = 'bonus';
    case Adjustment = 'adjustment';
    case Expiry = 'expiry';

    public function isCredit(): bool
    {
        return in_array($this, [self::Purchase, self::Refund, self::Bonus, self::Adjustment]);
    }
}
