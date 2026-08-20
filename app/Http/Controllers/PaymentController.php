<?php

namespace App\Http\Controllers;

use App\Models\CreditBundle;
use App\Services\Payments\PaymentGatewayException;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
    ) {}

    /** Send the professional to the hosted checkout. */
    public function checkout(Request $request, CreditBundle $bundle): RedirectResponse
    {
        if (! $bundle->is_active) {
            return back()->with('error', 'That bundle is not available.');
        }

        // Say so plainly rather than letting the gateway throw at the user.
        if (blank(config('services.paystack.secret'))) {
            return back()->with('error', 'Card payments are not switched on yet. Nothing has been charged.');
        }

        try {
            return redirect()->away(
                $this->payments->startPurchase($request->user(), $bundle)
            );
        } catch (PaymentGatewayException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Where Paystack sends the browser back. Deliberately does not grant
     * anything: it settles from the gateway's own answer, and the webhook is
     * still the authority if this request never happens.
     */
    public function callback(Request $request): RedirectResponse
    {
        $reference = (string) $request->query('reference', '');

        if ($reference === '') {
            return redirect()->route('professional.wallet');
        }

        try {
            $payment = $this->payments->settle($reference);
        } catch (PaymentGatewayException $e) {
            return redirect()->route('professional.wallet')
                ->with('warning', 'We are still confirming that payment. Your credits will appear shortly.');
        }

        return redirect()->route('professional.wallet')->with(
            $payment->isSettled() ? 'success' : 'error',
            $payment->isSettled()
                ? "Payment confirmed. {$payment->credits} credits added to your wallet."
                : ($payment->failure_reason ?: 'That payment did not go through.'),
        );
    }

    /**
     * The source of truth. Signature verified, then re-verified against the
     * gateway API, then settled idempotently.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();

        if (! $this->payments->signatureIsValid($payload, $request->header('x-paystack-signature'))) {
            Log::warning('Rejected a Paystack webhook with an invalid signature.');

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $reference = $request->input('data.reference');

        if (! is_string($reference) || $reference === '') {
            return response()->json(['message' => 'No reference.'], 422);
        }

        try {
            $this->payments->settle($reference);
        } catch (\Throwable $e) {
            Log::error('Paystack webhook could not be settled', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            // 500 so Paystack retries rather than dropping a real payment.
            return response()->json(['message' => 'Could not settle.'], 500);
        }

        return response()->json(['message' => 'ok']);
    }
}
