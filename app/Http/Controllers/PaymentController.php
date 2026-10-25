<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Show payment selection/processing view for a customer's order.
     */
    public function show(int $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->with('items.product')
            ->firstOrFail();

        if ($order->isPaid()) {
            return redirect()->route('account.orders.show', $order->id)
                ->with('info', 'Este pedido ya está pagado.');
        }

        if ($order->isCancelled()) {
            return redirect()->route('account.orders.show', $order->id)
                ->with('error', 'Este pedido está cancelado y no admite pago.');
        }

        $paymentDriver = config('localmarket.payment_driver', 'mock');
        $stripeKey = config('localmarket.stripe.key');

        return view('payment.show', compact('order', 'paymentDriver', 'stripeKey'));
    }

    /**
     * Process simulated / mock card payment.
     */
    public function processMock(Request $request, int $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $data = $request->validate([
            'card_number' => ['nullable', 'string'],
            'card_holder' => ['nullable', 'string', 'max:100'],
            'expiry' => ['nullable', 'string'],
            'cvv' => ['nullable', 'string', 'max:4'],
            'simulate_outcome' => ['required', 'in:success,failure'],
        ]);

        try {
            $payment = $this->paymentService->processMockPayment($order, [
                'outcome' => $data['simulate_outcome'],
                'card_number' => $data['card_number'] ?? '4242424242424242',
                'failure_reason' => 'Pago rechazado: simulación de fallo solicitada por el usuario.',
            ]);

            if ($payment->isCompleted()) {
                return redirect()->route('account.orders.show', $order->id)
                    ->with('success', '¡Pago procesado con éxito! Pedido confirmado y stock asignado.');
            }

            return back()->with('error', 'El pago ha sido rechazado por la entidad bancaria simulada. Por favor, revisa tus datos o inténtalo de nuevo.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Initialize Stripe PaymentIntent endpoint (called via fetch by checkout payment widget).
     */
    public function createStripeIntent(int $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        try {
            $intent = $this->paymentService->createStripePaymentIntent($order);

            return response()->json($intent);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
