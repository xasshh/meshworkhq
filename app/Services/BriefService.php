<?php

namespace App\Services;

use App\Enums\BriefStatus;
use App\Events\BriefPublished;
use App\Exceptions\BriefNotAvailableException;
use App\Models\Brief;
use App\Models\User;

final class BriefService
{
    /**
     * Transition a draft brief to published and fire the matching event.
     *
     * @throws BriefNotAvailableException
     */
    public function publish(Brief $brief): Brief
    {
        if ($brief->status !== BriefStatus::Draft && $brief->status !== BriefStatus::AiReview) {
            throw new BriefNotAvailableException(
                "Brief \"{$brief->title}\" cannot be published from status: {$brief->status->value}."
            );
        }

        $brief->update([
            'status' => BriefStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        BriefPublished::dispatch($brief);

        return $brief->refresh();
    }

    /**
     * Mark a brief as receiving pitches once the first unlock occurs.
     * Called internally — not by the client.
     */
    public function markReceivingPitches(Brief $brief): void
    {
        if ($brief->status === BriefStatus::Published) {
            $brief->update(['status' => BriefStatus::ReceivingPitches]);
        }
    }

    /**
     * Client shortlists — brief moves to shortlisting state.
     *
     * @throws BriefNotAvailableException
     */
    public function shortlist(Brief $brief): void
    {
        if ($brief->status !== BriefStatus::ReceivingPitches) {
            throw new BriefNotAvailableException(
                'Brief must be in receiving_pitches state to shortlist.'
            );
        }

        $brief->update(['status' => BriefStatus::Shortlisting]);
    }

    /**
     * Client hires a professional and closes the brief to new pitches.
     *
     * @throws BriefNotAvailableException
     */
    public function hire(Brief $brief, User $professional): Brief
    {
        if (! in_array($brief->status, [BriefStatus::ReceivingPitches, BriefStatus::Shortlisting])) {
            throw new BriefNotAvailableException(
                "Brief \"{$brief->title}\" is not in a hireable state."
            );
        }

        $brief->update([
            'status' => BriefStatus::Hired,
            'hired_professional_id' => $professional->id,
        ]);

        return $brief->refresh();
    }

    /**
     * Client manually closes a brief without hiring.
     *
     * @throws BriefNotAvailableException
     */
    public function close(Brief $brief): void
    {
        if (! $brief->status->isActive()) {
            throw new BriefNotAvailableException(
                "Brief \"{$brief->title}\" is not in an active state."
            );
        }

        $brief->update(['status' => BriefStatus::Closed]);
    }

    /**
     * Expire a brief that has passed its expires_at date.
     * Called by the ExpireOverdueBriefsJob scheduler.
     */
    public function expire(Brief $brief): void
    {
        if ($brief->status->isActive()) {
            $brief->update(['status' => BriefStatus::Expired]);
        }
    }

    /**
     * Bulk expire all overdue active briefs.
     */
    public function expireOverdue(): int
    {
        $count = 0;

        Brief::whereIn('status', [
            BriefStatus::Published->value,
            BriefStatus::ReceivingPitches->value,
            BriefStatus::Shortlisting->value,
        ])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->each(function (Brief $brief) use (&$count) {
                $this->expire($brief);
                $count++;
            });

        return $count;
    }
}
