<?php

namespace App\Services\Payments;

/**
 * What the gateway says about one transaction, reduced to the three things
 * that decide whether credits are awarded.
 */
final readonly class GatewayVerification
{
    public function __construct(
        public bool $successful,
        public int $amountKobo,
        public ?string $gatewayReference = null,
        public ?string $failureReason = null,
    ) {}
}
