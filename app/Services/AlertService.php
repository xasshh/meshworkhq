<?php

namespace App\Services;

use App\Enums\AlertStatus;
use App\Jobs\DispatchAlertWaveJob;
use App\Models\Alert;
use App\Models\Brief;
use App\Models\User;
use App\Notifications\BriefAlertNotification;
use Illuminate\Support\Collection;

final class AlertService
{
    public function __construct(
        private readonly MatchingService $matchingService,
    ) {}

    /**
     * Entry point called when a brief is published.
     * Finds all candidates and dispatches wave 1 immediately;
     * schedules waves 2 and 3 with delays per §6.3.
     */
    public function initiate(Brief $brief): void
    {
        $candidates = $this->matchingService->findCandidates($brief);

        if ($candidates->isEmpty()) {
            return;
        }

        $waves = $this->matchingService->splitIntoWaves($candidates);

        // Wave 1: immediate
        DispatchAlertWaveJob::dispatch($brief, $waves[1]->values(), 1);

        // Wave 2: after 6 hours (if wave 1 produces < 3 unlocks)
        if ($waves[2]->isNotEmpty()) {
            DispatchAlertWaveJob::dispatch($brief, $waves[2]->values(), 2)
                ->delay(now()->addHours(6));
        }

        // Wave 3: after 24 hours (if total unlocks < 5)
        if ($waves[3]->isNotEmpty()) {
            DispatchAlertWaveJob::dispatch($brief, $waves[3]->values(), 3)
                ->delay(now()->addHours(24));
        }
    }

    /**
     * Create Alert records and send notifications for one wave.
     *
     * @param  Collection<int, User>  $professionals
     */
    public function dispatchWave(Brief $brief, Collection $professionals, int $wave): void
    {
        // Guard: if brief is no longer active, skip.
        if (! $brief->isAvailableForUnlock()) {
            return;
        }

        // Guard: enforce anti-spam cap of 50 total alerts per brief.
        $alreadySent = Alert::where('brief_id', $brief->id)->count();
        $remaining = max(0, 50 - $alreadySent);
        $professionals = $professionals->take($remaining);

        foreach ($professionals as $professional) {
            // Anti-spam: max 20 alerts per professional per 24h.
            $todayCount = Alert::where('professional_id', $professional->id)
                ->where('notified_at', '>=', now()->subDay())
                ->count();

            if ($todayCount >= 20) {
                continue;
            }

            $alert = Alert::create([
                'brief_id' => $brief->id,
                'professional_id' => $professional->id,
                'wave' => $wave,
                'status' => AlertStatus::Notified,
                'notified_at' => now(),
            ]);

            $professional->notify(new BriefAlertNotification($alert));
        }

        $brief->increment('total_alerts_sent', $professionals->count());
        $brief->update(['alert_wave' => $wave]);
    }
}
