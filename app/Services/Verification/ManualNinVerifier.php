<?php

namespace App\Services\Verification;

/**
 * The default verifier, used until a licensed provider is configured.
 *
 * It reaches no external service and makes no decision: every submission is
 * routed to manual review. This is the honest fallback. Verifying identity is
 * something only a licensed provider can actually do, so guessing here would
 * mean handing out verified badges that mean nothing.
 *
 * To go live with automated checks, implement NinVerifier against your
 * provider and bind it in AppServiceProvider. Nothing else needs to change.
 */
final class ManualNinVerifier implements NinVerifier
{
    public function verify(string $nin, string $expectedName): NinVerificationResult
    {
        return NinVerificationResult::manualReview(
            lastFour: substr($nin, -4),
        );
    }
}
