<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class PaystackGateway implements PaymentGateway
{
    private const BASE_URL = 'https://api.paystack.co';

    public function __construct(
        private readonly ?string $secretKey,
        private readonly string $callbackUrl,
    ) {}

    public function initialize(Payment $payment): string
    {
        $response = $this->client()->post(self::BASE_URL.'/transaction/initialize', [
            'email' => $payment->user->email,
            'amount' => $payment->amount_kobo,
            'currency' => 'NGN',
            'reference' => $payment->reference,
            'callback_url' => $this->callbackUrl,
            'metadata' => [
                'user_id' => $payment->user_id,
                'credits' => $payment->credits,
                'bundle' => $payment->bundle?->name,
            ],
        ]);

        if (! $response->successful() || $response->json('status') !== true) {
            Log::error('Paystack initialize failed', [
                'reference' => $payment->reference,
                'status' => $response->status(),
                'message' => $response->json('message'),
            ]);

            throw new PaymentGatewayException(
                $response->json('message') ?? 'Could not start the payment. Please try again.'
            );
        }

        $url = $response->json('data.authorization_url');

        if (! is_string($url) || $url === '') {
            throw new PaymentGatewayException('The payment gateway did not return a checkout link.');
        }

        return $url;
    }

    public function verify(string $reference): GatewayVerification
    {
        $response = $this->client()->get(self::BASE_URL.'/transaction/verify/'.urlencode($reference));

        if (! $response->successful() || $response->json('status') !== true) {
            throw new PaymentGatewayException(
                $response->json('message') ?? 'Could not verify the payment.'
            );
        }

        $data = $response->json('data') ?? [];
        $status = $data['status'] ?? null;

        return new GatewayVerification(
            successful: $status === 'success',
            amountKobo: (int) ($data['amount'] ?? 0),
            gatewayReference: isset($data['id']) ? (string) $data['id'] : null,
            failureReason: $status === 'success' ? null : ($data['gateway_response'] ?? $status),
        );
    }

    /**
     * Paystack signs the raw body with HMAC SHA512 using the secret key.
     * Compared in constant time so the check cannot be timed.
     */
    public function signatureIsValid(string $payload, ?string $signature): bool
    {
        if ($signature === null || $signature === '' || ! $this->configured()) {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $payload, $this->secretKey), $signature);
    }

    public function configured(): bool
    {
        return is_string($this->secretKey) && $this->secretKey !== '';
    }

    private function client(): PendingRequest
    {
        if (! $this->configured()) {
            throw new PaymentGatewayException('Payments are not configured yet.');
        }

        return Http::withToken($this->secretKey)
            ->acceptJson()
            ->timeout(20)
            ->retry(2, 200, throw: false);
    }
}
