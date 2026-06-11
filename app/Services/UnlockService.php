<?php

namespace App\Services;

use App\Enums\AlertStatus;
use App\Enums\UnlockStatus;
use App\Events\BriefUnlocked;
use App\Exceptions\AlreadyUnlockedException;
use App\Exceptions\BriefNotAvailableException;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Alert;
use App\Models\Brief;
use App\Models\Conversation;
use App\Models\Unlock;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UnlockService
{
    public function __construct(
        private readonly CreditService $creditService,
    ) {}

    /**
     * Atomic unlock sequence from Phase 2 §5.4.
     *
     * @throws BriefNotAvailableException
     * @throws AlreadyUnlockedException
     * @throws InsufficientCreditsException
     */
    public function unlock(User $professional, Brief $brief): Unlock
    {
        return DB::transaction(function () use ($professional, $brief) {
            // Step 1: Brief must be in a pitchable state.
            if (! $brief->isAvailableForUnlock()) {
                throw new BriefNotAvailableException(
                    "Brief \"{$brief->title}\" is no longer accepting pitches."
                );
            }

            // Step 2: Sufficient credits.
            if (! $professional->hasCredits(1)) {
                throw new InsufficientCreditsException(1, $professional->credits);
            }

            // Step 3: Not already unlocked — return existing unlock if so.
            $existing = Unlock::where('brief_id', $brief->id)
                ->where('professional_id', $professional->id)
                ->first();

            if ($existing !== null) {
                throw new AlreadyUnlockedException;
            }

            // Step 4: Deduct credit atomically.
            $this->creditService->deduct(
                user: $professional,
                amount: 1,
                reference: $this->creditService->unlockReference($brief->id, $professional->id),
                description: "Unlock: \"{$brief->title}\"",
                related: $brief,
            );

            // Step 5: Create Unlock record.
            $alert = Alert::where('brief_id', $brief->id)
                ->where('professional_id', $professional->id)
                ->first();

            $unlock = Unlock::create([
                'brief_id' => $brief->id,
                'professional_id' => $professional->id,
                'alert_id' => $alert?->id,
                'credits_spent' => 1,
                'status' => UnlockStatus::Active,
                'unlocked_at' => now(),
            ]);

            // Step 6: Update alert status.
            if ($alert) {
                $alert->update(['status' => AlertStatus::Unlocked]);
            }

            // Step 7: Increment brief unlock counter.
            $brief->increment('total_unlocks');

            // Step 8: Create or retrieve conversation thread.
            Conversation::firstOrCreate(
                ['brief_id' => $brief->id, 'professional_id' => $professional->id],
                [
                    'client_id' => $brief->client_id,
                    'unlock_id' => $unlock->id,
                    'last_message_at' => null,
                ],
            );

            // Step 9: Fire event (notifies client, updates brief status).
            BriefUnlocked::dispatch($unlock);

            return $unlock;
        });
    }
}
