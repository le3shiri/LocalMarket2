<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Process a mock/simulated card payment.
     * Supports passing outcome = 'success' or 'failure' for complete QA and demo coverage.
     */
    public function processMockPayment(Order $order, array $paymentData = []): Payment
    {
        if ($order->isPaid()) {
            throw new Exception('Este pedido ya ha sido pagado previamente.');
        }

        if ($order->isCancelled()) {
            throw new Exception('No se puede procesar el pago de un pedido cancelado.');
        }

        $outcome = $paymentData['outcome'] ?? 'success';
        $cardNumber = preg_replace('/\s+/', '', (string) ($paymentData['card_number'] ?? '4242424242424242'));
        $last4 = substr($cardNumber, -4) ?: '4242';
        $cardBrand = $paymentData['card_brand'] ?? 'Visa';

        $txId = 'mock_'.date('YmdHis').'_'.bin2hex(random_bytes(4));

        if ($outcome !== 'success') {
            return $this->recordFailedPayment($order, Payment::PROVIDER_MOCK, $txId, $last4, $cardBrand, [
                'reason' => $paymentData['failure_reason'] ?? 'Fondos insuficientes o tarjeta rechazada por la entidad emisora.',
            ]);
        }

        return $this->recordSuccessfulPayment($order, Payment::PROVIDER_MOCK, $txId, $last4, $cardBrand, [
            'gateway' => 'LocalMarket Mock POS Gateway',
            'simulated' => true,
        ]);
    }

    /**
     * Create a Stripe PaymentIntent for test mode payments.
     */
    public function createStripePaymentIntent(Order $order): array
    {
        $stripeSecret = config('localmarket.stripe.secret');

        // If Stripe keys are not configured or set to mock, return mock fallback intent
        if (empty($stripeSecret) || str_starts_with($stripeSecret, 'mock_') || $stripeSecret === 'sk_test_placeholder') {
            $clientSecret = 'pi_mock_'.bin2hex(random_bytes(12)).'_secret_'.bin2hex(random_bytes(8));

            return [
                'id' => explode('_secret_', $clientSecret)[0],
                'client_secret' => $clientSecret,
                'amount' => (int) round($order->total * 100),
                'currency' => 'eur',
                'is_simulated' => true,
            ];
        }

        // Standard Stripe API integration via cURL / HTTP
        $payload = http_build_query([
            'amount' => (int) round($order->total * 100),
            'currency' => 'eur',
            'description' => "LocalMarket Pedido #{$order->number}",
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->number,
                'customer_email' => $order->customer_email,
            ],
        ]);

        $ch = curl_init('https://api.stripe.com/v1/payment_intents');
        curl_setopt($ch, CURLOPT_USERPWD, $stripeSecret.':');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode((string) $response, true);

        if ($httpCode >= 400 || empty($data['client_secret'])) {
            Log::error('Error creating Stripe PaymentIntent: '.($data['error']['message'] ?? 'Unknown error'));
            throw new Exception('Error al comunicarse con la pasarela Stripe: '.($data['error']['message'] ?? 'Respuesta inválida.'));
        }

        return [
            'id' => $data['id'],
            'client_secret' => $data['client_secret'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'is_simulated' => false,
        ];
    }

    /**
     * Process Stripe webhook event payload safely and idempotently.
     */
    public function handleStripeWebhook(array $event): ?Payment
    {
        $type = $event['type'] ?? '';
        $object = $event['data']['object'] ?? [];
        $orderId = $object['metadata']['order_id'] ?? null;

        if (! $orderId) {
            return null;
        }

        $order = Order::find($orderId);
        if (! $order) {
            return null;
        }

        $paymentIntentId = $object['id'] ?? null;
        $charge = $object['charges']['data'][0] ?? [];
        $last4 = $charge['payment_method_details']['card']['last4'] ?? null;
        $brand = $charge['payment_method_details']['card']['brand'] ?? 'Stripe Card';

        if ($type === 'payment_intent.succeeded') {
            if ($order->isPaid()) {
                return $order->latestPayment;
            }

            return $this->recordSuccessfulPayment(
                $order,
                Payment::PROVIDER_STRIPE,
                $paymentIntentId,
                $last4,
                $brand,
                $event
            );
        }

        if ($type === 'payment_intent.payment_failed') {
            return $this->recordFailedPayment(
                $order,
                Payment::PROVIDER_STRIPE,
                $paymentIntentId,
                $last4,
                $brand,
                $event
            );
        }

        return null;
    }

    /**
     * Record a successful payment atomically:
     * 1. Consumes the reserved stock (quantity -= qty, reserved -= qty).
     * 2. Sets order payment_status = 'paid' and status = 'confirmed'.
     * 3. Sends confirmation email.
     */
    public function recordSuccessfulPayment(
        Order $order,
        string $provider,
        string $transactionId,
        ?string $last4,
        ?string $brand,
        array $payload = []
    ): Payment {
        return DB::transaction(function () use ($order, $provider, $transactionId, $last4, $brand, $payload) {
            // Lock order for atomic payment update
            $order = Order::where('id', $order->id)->lockForUpdate()->first();

            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'status' => Payment::STATUS_COMPLETED,
                'currency' => 'EUR',
                'amount' => $order->total,
                'payment_method' => 'card',
                'card_last4' => $last4,
                'card_brand' => $brand,
                'payload' => $payload,
                'completed_at' => now(),
            ]);

            // Transition Order Status to confirmed
            $order->payment_status = Order::PAYMENT_PAID;
            $order->status = Order::STATUS_CONFIRMED;
            $order->paid_at = now();
            $order->save();

            // Consume stock reservation
            $onlineLocation = Location::where('is_online_source', true)->first();
            if ($onlineLocation) {
                $this->inventoryService->consumeReservation($order, $onlineLocation);
            }

            // Create OrderStatusHistory
            $order->statusHistories()->create([
                'from_status' => Order::STATUS_PENDING_PAYMENT,
                'to_status' => Order::STATUS_CONFIRMED,
                'user_id' => auth()->id() ?? $order->user_id,
                'note' => "Pago completado con éxito mediante {$provider} ({$brand} ****{$last4}). Transacción #{$transactionId}.",
            ]);

            AuditLog::record('payment.completed', $order, [
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'amount' => $order->total,
            ]);

            // Send order confirmation email
            $this->notificationService->sendOrderConfirmation($order);

            return $payment;
        });
    }

    /**
     * Record a failed payment attempt.
     */
    public function recordFailedPayment(
        Order $order,
        string $provider,
        string $transactionId,
        ?string $last4,
        ?string $brand,
        array $payload = []
    ): Payment {
        return DB::transaction(function () use ($order, $provider, $transactionId, $last4, $brand, $payload) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'status' => Payment::STATUS_FAILED,
                'currency' => 'EUR',
                'amount' => $order->total,
                'payment_method' => 'card',
                'card_last4' => $last4,
                'card_brand' => $brand,
                'payload' => $payload,
                'failed_at' => now(),
            ]);

            $order->update(['payment_status' => Order::PAYMENT_FAILED]);

            AuditLog::record('payment.failed', $order, [
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'amount' => $order->total,
            ]);

            return $payment;
        });
    }
}
