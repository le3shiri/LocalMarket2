<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Handle incoming Stripe webhook payload.
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('localmarket.stripe.webhook_secret');

        $event = null;

        // If webhook secret is configured and Stripe PHP library available, verify signature
        if (! empty($webhookSecret) && $webhookSecret !== 'whsec_placeholder' && class_exists(Webhook::class)) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
                $event = json_decode(json_encode($event), true);
            } catch (Exception $e) {
                Log::warning('Stripe webhook signature verification failed: '.$e->getMessage());

                return response()->json(['error' => 'Invalid webhook signature'], 400);
            }
        } else {
            // Fallback JSON decoding for mock / local test harness
            $event = json_decode($payload, true);
        }

        if (! is_array($event)) {
            return response()->json(['error' => 'Payload JSON inválido'], 400);
        }

        try {
            $this->paymentService->handleStripeWebhook($event);

            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            Log::error('Error processing Stripe webhook: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
