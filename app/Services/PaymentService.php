<?php

namespace App\Services;

use App\Enums\CreditTransactionType;
use App\Enums\PaymentStatus;
use App\Models\CreditBundle;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentGatewayException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly CreditService $credits,
    ) {}

    /**
     * Record the attempt, then hand back the hosted checkout URL.
     *
     * @throws PaymentGatewayException
     */
    public function startPurchase(User $user, CreditBundle $bundle): string
    {
        $payment = Payment::create([
            'user_id' => $user->id,
            'credit_bundle_id' => $bundle->id,
            'reference' => 'mhq_'.Str::lower((string) Str::ulid()),
            'amount_kobo' => $bundle->price_kobo,
            'credits' => $bundle->credits,
            'status' => PaymentStatus::Pending,
        ]);

        try {
            return $this->gateway->initialize($payment->load('user', 'bundle'));
        } catch (PaymentGatewayException $e) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'failure_reason' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Settle a payment against what the gateway says, not what the browser or
     * the webhook body claims.
     *
     * Safe to call repeatedly: an already settled payment returns untouched,
     * and the credit award is keyed on the payment reference, so the ledger
     * refuses a second grant even if two callers race.
     */
    public function settle(string $reference): Payment
    {
        $payment = Payment::where('reference', $reference)->firstOrFail();

        if ($payment->isSettled()) {
            return $payment;
        }

        $verification = $this->gateway->verify($reference);

        if (! $verification->successful) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'failure_reason' => $verification->failureReason,
            ]);

            return $payment;
        }

        // Guard against a tampered or mismatched amount: never grant credits
        // for less money than the bundle costs.
        if ($verification->amountKobo < $payment->amount_kobo) {
            Log::warning('Paystack amount mismatch', [
                'reference' => $reference,
                'expected' => $payment->amount_kobo,
                'received' => $verification->amountKobo,
            ]);

            $payment->update([
                'status' => PaymentStatus::Failed,
                'failure_reason' => 'The amount paid did not match the bundle price.',
            ]);

            return $payment;
        }

        return DB::transaction(function () use ($payment, $verification) {
            $payment->update([
                'status' => PaymentStatus::Successful,
                'gateway_reference' => $verification->gatewayReference,
                'paid_at' => now(),
                'failure_reason' => null,
            ]);

            $this->credits->award(
                user: $payment->user,
                amount: $payment->credits,
                type: CreditTransactionType::Purchase,
                reference: $payment->reference,
                description: $payment->bundle
                    ? "Purchased {$payment->bundle->name}, {$payment->credits} credits"
                    : "Purchased {$payment->credits} credits",
                related: $payment,
            );

            return $payment->refresh();
        });
    }

    public function signatureIsValid(string $payload, ?string $signature): bool
    {
        return $this->gateway->signatureIsValid($payload, $signature);
    }
}
