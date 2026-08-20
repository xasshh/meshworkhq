<?php

namespace App\Services\Verification;

interface NinVerifier
{
    /**
     * Check a National Identification Number against a licensed provider.
     *
     * Implementations must never return, log or persist the raw NIN. Only the
     * outcome, the legal name the provider holds, and the last four digits.
     *
     * @param  string  $nin  the 11 digit number, used and discarded
     * @param  string  $expectedName  the name on the Meshwork HQ account, for a mismatch check
     */
    public function verify(string $nin, string $expectedName): NinVerificationResult;
}
