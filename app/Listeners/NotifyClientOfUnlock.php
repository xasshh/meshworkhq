<?php

namespace App\Listeners;

use App\Events\BriefUnlocked;
use App\Notifications\BriefUnlockedNotification;
use App\Services\BriefService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyClientOfUnlock implements ShouldQueue
{
    public function __construct(
        private readonly BriefService $briefService,
    ) {}

    public function handle(BriefUnlocked $event): void
    {
        $unlock = $event->unlock;
        $brief = $unlock->brief;
        $client = $brief->client;

        // Advance brief status from published → receiving_pitches on first unlock.
        $this->briefService->markReceivingPitches($brief);

        $client->notify(new BriefUnlockedNotification($unlock));
    }
}
