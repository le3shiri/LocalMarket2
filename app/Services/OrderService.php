<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CartService $cartService
    ) {}

    /**
     * Create an order from current cart and reserve stock atomically.
     */
    public function createOrder(User $customer, array $checkoutData): Order
    {
        $cart = $this->cartService->getCart();
        if (empty($cart['items'])) {
            throw new InvalidArgumentException('La cesta está vacía.');
        }

        $onlineLocation = Location::where('is_online_source', true)->first();
        if (! $onlineLocation) {
            throw new Exception('No hay una ubicación online configurada para tramitar pedidos.');
        }

        // Validate delivery address / postcode if delivery chosen
        $fulfilmentMethod = $checkoutData['fulfilment_method'] ?? Order::FULFILMENT_DELIVERY;
        $deliveryFee = 0.00;

        if ($fulfilmentMethod === Order::FULFILMENT_DELIVERY) {
            $postcode = trim($checkoutData['postcode'] ?? '');
            $allowedPostcodes = config('localmarket.delivery_postcodes', []);

            if (! in_array($postcode, $allowedPostcodes, true)) {
                throw new InvalidArgumentException('El código postal '.$postcode.' no está dentro del área de reparto en Barcelona ciudad (08001–08042).');
            }

            $freeFrom = (float) config('localmarket.free_delivery_from', 50.0);
            if ($cart['subtotal'] < $freeFrom) {
                $deliveryFee = (float) config('localmarket.delivery_fee', 4.95);
            }
        }

        $subtotal = $cart['subtotal'];
        $discount = 0.00; // Coupons handled in Sprint 6
        $total = round($subtotal - $discount + $deliveryFee, 2);

        return DB::transaction(function () use ($customer, $checkoutData, $cart, $onlineLocation, $fulfilmentMethod, $subtotal, $discount, $deliveryFee, $total) {
            // Generate unique order number (BR-ORD-01): LM-YYYYMMDD-XXXXX
            $today = date('Ymd');
            $random = strtoupper(substr(uniqid(), -5));
            $orderNumber = "LM-{$today}-{$random}";

            // 1. Create order record in pending_payment
            $order = Order::create([
                'number' => $orderNumber,
                'user_id' => $customer->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'payment_status' => Order::PAYMENT_PENDING,
                'fulfilment_method' => $fulfilmentMethod,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'customer_name' => $checkoutData['customer_name'] ?? $customer->name,
                'customer_email' => $checkoutData['customer_email'] ?? $customer->email,
                'phone' => $checkoutData['phone'] ?? $customer->phone,
                'address' => $checkoutData['address'] ?? null,
                'postcode' => $checkoutData['postcode'] ?? null,
                'city' => $checkoutData['city'] ?? 'Barcelona',
                'notes' => $checkoutData['notes'] ?? null,
            ]);

            // 2. Reserve stock with row locks and save line items
            foreach ($cart['items'] as $item) {
                $product = $item['product'];
                $quantity = (float) $item['quantity'];

                // reserveStock throws exception if insufficient, rolling back transaction
                $this->inventoryService->reserveStock($product, $onlineLocation, $quantity, $order->id);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sale_unit' => $product->sale_unit,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'line_total' => $item['line_total'],
                ]);
            }

            // 3. Record status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => Order::STATUS_PENDING_PAYMENT,
                'user_id' => $customer->id,
                'note' => 'Pedido tramitado y stock reservado. Pendiente de pago.',
            ]);

            // 4. Audit
            AuditLog::record('order.created', $order, [
                'number' => $order->number,
                'total' => $order->total,
                'fulfilment' => $order->fulfilment_method,
            ]);

            // Clear customer cart
            $this->cartService->clear();

            return $order;
        });
    }

    /**
     * Change order status following state machine rules (BR-ORD-04).
     */
    public function changeStatus(Order $order, string $newStatus, ?User $user = null, ?string $note = null): Order
    {
        $allowed = Order::TRANSITIONS[$order->status] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw new InvalidArgumentException("Transición de estado no permitida: de {$order->status} a {$newStatus}.");
        }

        // Verify pickup vs delivery path
        if ($newStatus === Order::STATUS_READY_FOR_PICKUP && ! $order->isPickup()) {
            throw new InvalidArgumentException('El estado "Listo para recoger" solo aplica a pedidos con recogida en tienda.');
        }

        if ($newStatus === Order::STATUS_OUT_FOR_DELIVERY && ! $order->isDelivery()) {
            throw new InvalidArgumentException('El estado "En reparto" solo aplica a pedidos con envío a domicilio.');
        }

        return DB::transaction(function () use ($order, $newStatus, $user, $note) {
            $oldStatus = $order->status;
            $order->status = $newStatus;

            $onlineLocation = Location::where('is_online_source', true)->first();

            // Handling cancellations
            if ($newStatus === Order::STATUS_CANCELLED) {
                $order->cancelled_at = now();

                if ($order->isPaid()) {
                    // Paid order cancelled: admin returns physical stock and marks for refund (BR-INV-08 / BR-ORD-04)
                    if ($onlineLocation) {
                        $this->inventoryService->returnStock($order, $onlineLocation);
                    }
                    $order->payment_status = Order::PAYMENT_REFUNDED;
                } else {
                    // Unpaid order cancelled: release reserved stock (BR-INV-07)
                    if ($onlineLocation) {
                        $this->inventoryService->releaseReservation($order, $onlineLocation);
                    }
                }
            }

            $order->save();

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'user_id' => $user?->id,
                'note' => $note ?: "Cambio de estado a {$order->statusLabel()}",
            ]);

            AuditLog::record('order.status_changed', $order, [
                'from' => $oldStatus,
                'to' => $newStatus,
                'user' => $user?->name ?? 'Sistema',
            ]);

            return $order;
        });
    }
}
