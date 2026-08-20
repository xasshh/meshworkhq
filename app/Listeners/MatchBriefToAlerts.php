<?php

namespace App\Listeners;

use App\Events\BriefPublished;
use App\Notifications\BriefPublishedNotification;
use App\Services\AlertService;
use App\Services\MatchingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class MatchBriefToAlerts implements ShouldQueue
{
    public string $queue = 'alerts';

    public function __construct(
        private readonly AlertService $alertService,
        private readonly MatchingService $matchingService,
    ) {}

    public function handle(BriefPublished $event): void
    {
        $matched = $this->matchingService->findCandidates($event->brief)->count();

        $this->alertService->initiate($event->brief);

        // Tell the client what their brief actually reached, rather than
        // leaving them to guess whether anything happened.
        $event->brief->client->notify(
            new BriefPublishedNotification($event->brief, $matched)
        );
    }
}
