<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    /**
     * Start a charge and return the hosted checkout URL to send the user to.
     *
     * @throws PaymentGatewayException
     */
    public function initialize(Payment $payment): string;

    /**
     * Ask the gateway what actually happened. Never trust the browser.
     *
     * @throws PaymentGatewayException
     */
    public function verify(string $reference): GatewayVerification;

    /**
     * Confirm a webhook payload really came from the gateway.
     */
    public function signatureIsValid(string $payload, ?string $signature): bool;
}
