<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Brief;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class MatchingService
{
    /**
     * Hard-filtering stage (Phase 2 §6.1).
     * Returns professionals who pass all hard rules for this brief,
     * excluding anyone already alerted.
     *
     * @return Collection<int, User>
     */
    public function findCandidates(Brief $brief, int $limit = 50): Collection
    {
        $alreadyAlerted = Alert::where('brief_id', $brief->id)
            ->pluck('professional_id');

        $query = User::query()
            ->where('role', 'professional')
            ->whereNotIn('id', $alreadyAlerted)
            ->where('credits', '>=', 0);

        // Rule: Profile must be >= 70% complete (alert-ready). Enforced in SQL
        // by User::scopeProfileReady, which is the query side of
        // User::isProfileReady, so what the dashboard promises a professional
        // is exactly what decides whether they are alerted.
        $query->profileReady();

        // Rule: Skill overlap — brief skill_tags must intersect professional skill_tags.
        if (! empty($brief->skill_tags)) {
            $query->where(function (Builder $q) use ($brief) {
                foreach ($brief->skill_tags as $tag) {
                    $q->orWhereJsonContains('skill_tags', $tag);
                }
            });
        }

        // Rule: Geography — if brief is location-specific, match location.
        if (! $brief->is_remote && $brief->location) {
            $query->where(function (Builder $q) use ($brief) {
                $q->whereRaw('LOWER(company_role) LIKE ?', ['%'.strtolower($brief->location).'%'])
                    ->orWhereNull('company_role'); // location field on pro is stored in company_role for now
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Split candidates into wave batches per §6.3.
     *
     * Wave 1 (immediate): top 10
     * Wave 2 (6h delay):  positions 11–25
     * Wave 3 (24h delay): positions 26–50
     *
     * @param  Collection<int, User>  $candidates
     * @return array{1: Collection<int, User>, 2: Collection<int, User>, 3: Collection<int, User>}
     */
    public function splitIntoWaves(Collection $candidates): array
    {
        return [
            1 => $candidates->take(10),
            2 => $candidates->slice(10, 15),
            3 => $candidates->slice(25),
        ];
    }
}
