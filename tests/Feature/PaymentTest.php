<?php

use App\Enums\CreditTransactionType;
use App\Enums\PaymentStatus;
use App\Models\CreditBundle;
use App\Models\CreditTransaction;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\PaymentGatewayException;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.paystack.secret', 'sk_test_secret');

    $this->bundle = CreditBundle::create([
        'name' => 'Starter Pack',
        'credits' => 5,
        'price_kobo' => 250_000,
        'is_active' => true,
        'sort_order' => 1,
    ]);
});

function paystackVerify(string $status, int $amount): array
{
    return [
        'status' => true,
        'data' => ['status' => $status, 'amount' => $amount, 'id' => 998877, 'gateway_response' => 'Declined'],
    ];
}

test('starting a purchase records a pending payment and returns the checkout url', function () {
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/abc123'],
        ]),
    ]);

    $professional = User::factory()->professional()->create(['credits' => 0]);

    $url = app(PaymentService::class)->startPurchase($professional, $this->bundle);

    expect($url)->toBe('https://checkout.paystack.com/abc123');

    $payment = Payment::firstOrFail();
    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->amount_kobo)->toBe(250_000)
        ->and($payment->credits)->toBe(5)
        ->and($payment->user_id)->toBe($professional->id);

    // Nothing is granted before the money is confirmed.
    expect($professional->refresh()->credits)->toBe(0);
});

test('a gateway failure marks the payment failed and grants nothing', function () {
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response(['status' => false, 'message' => 'Invalid key'], 401),
    ]);

    $professional = User::factory()->professional()->create(['credits' => 0]);

    expect(fn () => app(PaymentService::class)->startPurchase($professional, $this->bundle))
        ->toThrow(PaymentGatewayException::class);

    expect(Payment::firstOrFail()->status)->toBe(PaymentStatus::Failed)
        ->and($professional->refresh()->credits)->toBe(0);
});

test('settling a successful payment awards the credits once', function () {
    Http::fake(['api.paystack.co/transaction/verify/*' => Http::response(paystackVerify('success', 250_000))]);

    $professional = User::factory()->professional()->create(['credits' => 0]);
    $payment = Payment::create([
        'user_id' => $professional->id,
        'credit_bundle_id' => $this->bundle->id,
        'reference' => 'mhq_test_ref',
        'amount_kobo' => 250_000,
        'credits' => 5,
        'status' => PaymentStatus::Pending,
    ]);

    app(PaymentService::class)->settle($payment->reference);

    expect($professional->refresh()->credits)->toBe(5)
        ->and($payment->refresh()->status)->toBe(PaymentStatus::Successful)
        ->and($payment->paid_at)->not->toBeNull();

    $ledger = CreditTransaction::where('user_id', $professional->id)->get();
    expect($ledger)->toHaveCount(1)
        ->and($ledger->first()->type)->toBe(CreditTransactionType::Purchase)
        ->and($ledger->first()->reference)->toBe('mhq_test_ref');
});

test('settling twice never grants credits twice', function () {
    Http::fake(['api.paystack.co/transaction/verify/*' => Http::response(paystackVerify('success', 250_000))]);

    $professional = User::factory()->professional()->create(['credits' => 0]);
    Payment::create([
        'user_id' => $professional->id,
        'credit_bundle_id' => $this->bundle->id,
        'reference' => 'mhq_replay',
        'amount_kobo' => 250_000,
        'credits' => 5,
        'status' => PaymentStatus::Pending,
    ]);

    $service = app(PaymentService::class);
    $service->settle('mhq_replay');
    $service->settle('mhq_replay');
    $service->settle('mhq_replay');

    expect($professional->refresh()->credits)->toBe(5)
        ->and(CreditTransaction::where('user_id', $professional->id)->count())->toBe(1);
});

test('a failed verification grants nothing', function () {
    Http::fake(['api.paystack.co/transaction/verify/*' => Http::response(paystackVerify('failed', 250_000))]);

    $professional = User::factory()->professional()->create(['credits' => 0]);
    Payment::create([
        'user_id' => $professional->id,
        'credit_bundle_id' => $this->bundle->id,
        'reference' => 'mhq_failed',
        'amount_kobo' => 250_000,
        'credits' => 5,
        'status' => PaymentStatus::Pending,
    ]);

    app(PaymentService::class)->settle('mhq_failed');

    expect($professional->refresh()->credits)->toBe(0)
        ->and(Payment::firstOrFail()->status)->toBe(PaymentStatus::Failed);
});

test('underpaying grants nothing even when the gateway says success', function () {
    Http::fake(['api.paystack.co/transaction/verify/*' => Http::response(paystackVerify('success', 100))]);

    $professional = User::factory()->professional()->create(['credits' => 0]);
    Payment::create([
        'user_id' => $professional->id,
        'credit_bundle_id' => $this->bundle->id,
        'reference' => 'mhq_short',
        'amount_kobo' => 250_000,
        'credits' => 5,
        'status' => PaymentStatus::Pending,
    ]);

    app(PaymentService::class)->settle('mhq_short');

    expect($professional->refresh()->credits)->toBe(0)
        ->and(Payment::firstOrFail()->status)->toBe(PaymentStatus::Failed);
});

test('a webhook without a valid signature is rejected and grants nothing', function () {
    $professional = User::factory()->professional()->create(['credits' => 0]);
    Payment::create([
        'user_id' => $professional->id,
        'credit_bundle_id' => $this->bundle->id,
        'reference' => 'mhq_unsigned',
        'amount_kobo' => 250_000,
        'credits' => 5,
        'status' => PaymentStatus::Pending,
    ]);

    $this->postJson(route('webhooks.paystack'), ['data' => ['reference' => 'mhq_unsigned']], [
        'x-paystack-signature' => 'clearly-not-right',
    ])->assertStatus(401);

    expect($professional->refresh()->credits)->toBe(0);
});

test('a correctly signed webhook settles the payment', function () {
    Http::fake(['api.paystack.co/transaction/verify/*' => Http::response(paystackVerify('success', 250_000))]);

    $professional = User::factory()->professional()->create(['credits' => 0]);
    Payment::create([
        'user_id' => $professional->id,
        'credit_bundle_id' => $this->bundle->id,
        'reference' => 'mhq_signed',
        'amount_kobo' => 250_000,
        'credits' => 5,
        'status' => PaymentStatus::Pending,
    ]);

    $payload = json_encode(['event' => 'charge.success', 'data' => ['reference' => 'mhq_signed']]);
    $signature = hash_hmac('sha512', $payload, 'sk_test_secret');

    $this->call('POST', route('webhooks.paystack'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
    ], $payload)->assertOk();

    expect($professional->refresh()->credits)->toBe(5)
        ->and(Payment::firstOrFail()->status)->toBe(PaymentStatus::Successful);
});

test('the wallet always offers the bundles for purchase', function () {
    $professional = User::factory()->professional()->create(['credits' => 0]);

    Livewire::actingAs($professional)
        ->test('pages::professional.wallet')
        ->assertSee('Starter Pack')
        ->assertSee('Buy this pack');
});

test('checkout says so plainly when no gateway key is configured', function () {
    config()->set('services.paystack.secret', null);

    $professional = User::factory()->professional()->create(['credits' => 0]);

    $this->actingAs($professional)
        ->from(route('professional.wallet'))
        ->post(route('professional.wallet.checkout', ['bundle' => $this->bundle->id]))
        ->assertRedirect(route('professional.wallet'))
        ->assertSessionHas('error');

    // Crucially, no half finished payment row is left behind.
    expect(Payment::count())->toBe(0)
        ->and($professional->refresh()->credits)->toBe(0);
});

test('a client cannot reach the checkout route', function () {
    $client = User::factory()->client()->create();

    $this->actingAs($client)
        ->post(route('professional.wallet.checkout', ['bundle' => $this->bundle->id]))
        ->assertForbidden();
});
