<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'credit_bundle_id', 'reference', 'gateway',
        'gateway_reference', 'amount_kobo', 'credits',
        'status', 'paid_at', 'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(CreditBundle::class, 'credit_bundle_id');
    }

    public function isSettled(): bool
    {
        return $this->status === PaymentStatus::Successful;
    }

    public function amountNaira(): float
    {
        return $this->amount_kobo / 100;
    }
}
