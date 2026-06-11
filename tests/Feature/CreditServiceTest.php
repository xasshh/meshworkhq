<?php

use App\Enums\CreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('award increases user credits and creates transaction', function () {
    $user = User::factory()->professional()->create(['credits' => 0]);
    $service = app(CreditService::class);

    $tx = $service->award(
        user: $user,
        amount: 5,
        type: CreditTransactionType::Bonus,
        reference: 'test-award-1',
        description: 'Test award',
    );

    expect($user->fresh()->credits)->toBe(5)
        ->and($tx->amount)->toBe(5)
        ->and($tx->balance_after)->toBe(5)
        ->and($tx->reference)->toBe('test-award-1');
});

test('award is idempotent on duplicate reference', function () {
    $user = User::factory()->professional()->create(['credits' => 0]);
    $service = app(CreditService::class);

    $service->award(user: $user, amount: 5, type: CreditTransactionType::Bonus, reference: 'dupe-ref');
    $service->award(user: $user, amount: 5, type: CreditTransactionType::Bonus, reference: 'dupe-ref');

    expect($user->fresh()->credits)->toBe(5);
    expect(CreditTransaction::where('reference', 'dupe-ref')->count())->toBe(1);
});

test('deduct decreases user credits and creates transaction', function () {
    $user = User::factory()->professional()->create(['credits' => 10]);
    $service = app(CreditService::class);

    $tx = $service->deduct(user: $user, amount: 3, reference: 'test-deduct-1');

    expect($user->fresh()->credits)->toBe(7)
        ->and($tx->amount)->toBe(-3)
        ->and($tx->balance_after)->toBe(7);
});

test('deduct throws when insufficient credits', function () {
    $user = User::factory()->professional()->create(['credits' => 1]);

    expect(fn () => app(CreditService::class)->deduct(user: $user, amount: 5, reference: 'fail-ref'))
        ->toThrow(InsufficientCreditsException::class);

    expect($user->fresh()->credits)->toBe(1);
});

test('deduct is idempotent on duplicate reference', function () {
    $user = User::factory()->professional()->create(['credits' => 10]);
    $service = app(CreditService::class);

    $service->deduct(user: $user, amount: 3, reference: 'dupe-deduct');
    $service->deduct(user: $user, amount: 3, reference: 'dupe-deduct');

    expect($user->fresh()->credits)->toBe(7);
});

test('welcome bonus awards 3 credits with expiry', function () {
    $user = User::factory()->professional()->create(['credits' => 0]);

    $tx = app(CreditService::class)->issueWelcomeBonus($user);

    expect($user->fresh()->credits)->toBe(3)
        ->and($tx->expires_at)->not->toBeNull();
});

test('refund awards credits back to user', function () {
    $user = User::factory()->professional()->create(['credits' => 10]);
    $service = app(CreditService::class);

    $original = $service->deduct(user: $user, amount: 1, reference: 'spend-1');
    $service->refund($user, $original);

    expect($user->fresh()->credits)->toBe(10);
});
