<?php

use App\Models\Brief;

test('an unpublished brief reports wave one', function () {
    $brief = Brief::factory()->create(['published_at' => null]);

    expect($brief->currentWave())->toBe(1)
        ->and($brief->waveAudience())->toBe(10)
        ->and($brief->nextWaveAt())->toBeNull();
});

test('wave advances as the brief ages', function (int $hoursLive, int $expectedWave, int $expectedAudience) {
    $brief = Brief::factory()->published()->create([
        'published_at' => now()->subHours($hoursLive),
    ]);

    expect($brief->currentWave())->toBe($expectedWave)
        ->and($brief->waveAudience())->toBe($expectedAudience);
})->with([
    'just published' => [0, 1, 10],
    'five hours in' => [5, 1, 10],
    'six hours in' => [6, 2, 25],
    'twenty three hours in' => [23, 2, 25],
    'twenty four hours in' => [24, 3, 50],
    'a week in' => [168, 3, 50],
]);

test('next wave time is six hours after publication during wave one', function () {
    $publishedAt = now()->subHours(2);
    $brief = Brief::factory()->published()->create(['published_at' => $publishedAt]);

    expect($brief->nextWaveAt()->timestamp)->toBe($publishedAt->addHours(6)->timestamp);
});

test('the final wave has no next wave', function () {
    $brief = Brief::factory()->published()->create(['published_at' => now()->subHours(30)]);

    expect($brief->nextWaveAt())->toBeNull()
        ->and($brief->waveProgress())->toBe(1.0);
});

test('wave progress reports how far through the window we are', function () {
    $brief = Brief::factory()->published()->create(['published_at' => now()->subHours(3)]);

    // Three hours into a six hour window.
    expect($brief->waveProgress())->toBeGreaterThan(0.45)
        ->and($brief->waveProgress())->toBeLessThan(0.55);
});
