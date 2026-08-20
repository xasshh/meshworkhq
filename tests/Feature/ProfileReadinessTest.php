<?php

use App\Models\Brief;
use App\Models\User;
use App\Services\MatchingService;

/**
 * The dashboard tells a professional "alerts are paused below 70%". That claim
 * is only true if the matcher enforces the same rule the profile page reports,
 * and the two are separate implementations: one in PHP over a loaded model, one
 * in SQL so the matcher can select and limit in a single query. These tests
 * exist to stop the two drifting apart again.
 */

/** The five fields a professional can leave blank; name and email always exist. */
function optionalProfileFields(): array
{
    return ['professional_title', 'phone', 'bio', 'portfolio_url', 'skill_tags'];
}

function fillProfile(array $fields): User
{
    $values = [];

    foreach ($fields as $field) {
        $values[$field] = $field === 'skill_tags' ? ['design'] : 'filled';
    }

    return User::factory()->professional()->create($values);
}

it('agrees between the PHP check and the SQL scope for every profile shape', function () {
    $optional = optionalProfileFields();

    // Every subset of the optional fields, so each completeness level is covered.
    for ($mask = 0; $mask < 2 ** count($optional); $mask++) {
        $fields = [];

        foreach ($optional as $bit => $field) {
            if ($mask & (1 << $bit)) {
                $fields[] = $field;
            }
        }

        fillProfile($fields);
    }

    $expected = User::all()->filter->isProfileReady()->pluck('id')->sort()->values();
    $actual = User::query()->profileReady()->pluck('id')->sort()->values();

    expect($actual->all())->toBe($expected->all())
        ->and($expected)->not->toBeEmpty()
        ->and($expected->count())->toBeLessThan(User::count());
});

it('treats an empty skill list and a blank string as unfilled', function () {
    $blank = User::factory()->professional()->create([
        'professional_title' => 'Designer',
        'phone' => '08000000000',
        'bio' => '',
        'skill_tags' => [],
    ]);

    expect($blank->isProfileReady())->toBeFalse()
        ->and(User::query()->profileReady()->whereKey($blank->id)->exists())->toBeFalse();
});

it('alerts a professional whose profile reaches the threshold', function () {
    $ready = fillProfile(['professional_title', 'bio', 'skill_tags']);
    $ready->update(['skill_tags' => ['design']]);

    expect($ready->fresh()->isProfileReady())->toBeTrue();

    $brief = Brief::factory()->published()->create(['skill_tags' => ['design']]);

    $candidates = app(MatchingService::class)->findCandidates($brief);

    expect($candidates->pluck('id'))->toContain($ready->id);
});

it('does not alert a professional below the threshold even on a perfect skill match', function () {
    // Skills match the brief exactly, but only three of seven fields are filled.
    $incomplete = User::factory()->professional()->create([
        'professional_title' => null,
        'phone' => null,
        'bio' => null,
        'portfolio_url' => null,
        'skill_tags' => ['design'],
    ]);

    expect($incomplete->isProfileReady())->toBeFalse();

    $brief = Brief::factory()->published()->create(['skill_tags' => ['design']]);

    $candidates = app(MatchingService::class)->findCandidates($brief);

    expect($candidates->pluck('id'))->not->toContain($incomplete->id);
});
