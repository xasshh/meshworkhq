<?php

namespace App\Services;

use App\Enums\CreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreditService
{
    private const BONUS_EXPIRY_DAYS = 90;

    /**
     * Award credits to a user (purchase, bonus, refund, adjustment).
     * Idempotent: duplicate references are silently ignored.
     */
    public function award(
        User $user,
        int $amount,
        CreditTransactionType $type,
        string $reference,
        ?string $description = null,
        ?\DateTimeInterface $expiresAt = null,
        mixed $related = null,
    ): CreditTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Award amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $reference, $description, $expiresAt, $related) {
            if (CreditTransaction::where('reference', $reference)->exists()) {
                return CreditTransaction::where('reference', $reference)->firstOrFail();
            }

            $newBalance = $user->credits + $amount;

            $user->increment('credits', $amount);

            return CreditTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference' => $reference,
                'description' => $description,
                'related_type' => $related ? get_class($related) : null,
                'related_id' => $related?->id,
                'expires_at' => $expiresAt,
            ]);
        });
    }

    /**
     * Deduct credits from a user (spend).
     *
     * @throws InsufficientCreditsException
     */
    public function deduct(
        User $user,
        int $amount,
        string $reference,
        ?string $description = null,
        mixed $related = null,
    ): CreditTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Deduct amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $reference, $description, $related) {
            $user->refresh();

            if ($user->credits < $amount) {
                throw new InsufficientCreditsException($amount, $user->credits);
            }

            if (CreditTransaction::where('reference', $reference)->exists()) {
                return CreditTransaction::where('reference', $reference)->firstOrFail();
            }

            $newBalance = $user->credits - $amount;

            $user->decrement('credits', $amount);

            return CreditTransaction::create([
                'user_id' => $user->id,
                'type' => CreditTransactionType::Spend,
                'amount' => -$amount,
                'balance_after' => $newBalance,
                'reference' => $reference,
                'description' => $description,
                'related_type' => $related ? get_class($related) : null,
                'related_id' => $related?->id,
            ]);
        });
    }

    /**
     * Issue a credit refund, referencing the original transaction.
     */
    public function refund(User $user, CreditTransaction $original, ?string $reason = null): CreditTransaction
    {
        $reference = 'refund-'.$original->reference;

        return $this->award(
            user: $user,
            amount: abs($original->amount),
            type: CreditTransactionType::Refund,
            reference: $reference,
            description: $reason ?? "Refund for: {$original->description}",
            related: $original,
        );
    }

    /**
     * Issue welcome bonus credits with 30-day expiry.
     */
    public function issueWelcomeBonus(User $user): CreditTransaction
    {
        return $this->award(
            user: $user,
            amount: 3,
            type: CreditTransactionType::Bonus,
            reference: 'welcome-bonus-'.$user->id,
            description: 'Welcome bonus — 3 free unlocks',
            expiresAt: now()->addDays(30),
        );
    }

    /**
     * Issue profile completion bonus.
     */
    public function issueProfileBonus(User $user): CreditTransaction
    {
        return $this->award(
            user: $user,
            amount: 2,
            type: CreditTransactionType::Bonus,
            reference: 'profile-complete-'.$user->id,
            description: '100% profile completion bonus',
            expiresAt: now()->addDays(self::BONUS_EXPIRY_DAYS),
        );
    }

    /**
     * Issue referral bonus credits.
     */
    public function issueReferralBonus(User $referrer, User $referred): CreditTransaction
    {
        return $this->award(
            user: $referrer,
            amount: 5,
            type: CreditTransactionType::Bonus,
            reference: 'referral-bonus-'.$referrer->id.'-'.$referred->id,
            description: "Referral bonus for inviting {$referred->name}",
            expiresAt: now()->addDays(self::BONUS_EXPIRY_DAYS),
        );
    }

    /**
     * Generate a unique reference for an unlock spend.
     */
    public function unlockReference(int $briefId, int $professionalId): string
    {
        return "unlock-{$briefId}-{$professionalId}";
    }
}
