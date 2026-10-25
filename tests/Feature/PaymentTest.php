<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Location $store;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->store = Location::create([
            'name' => 'Tienda Principal',
            'type' => Location::TYPE_STORE,
            'address' => 'Carrer de Mallorca 100, Barcelona',
            'is_online_source' => true,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Frutería',
            'slug' => 'fruteria',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cerezas del Jerte 500g',
            'slug' => 'cerezas-del-jerte',
            'price' => 4.50,
            'sale_unit' => Product::UNIT_PACK,
            'is_active' => true,
        ]);

        StockLevel::create([
            'location_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 20.00,
            'reserved' => 0.00,
        ]);

        $this->customer = User::factory()->create([
            'role' => User::CUSTOMER,
            'is_active' => true,
        ]);
    }

    private function createPendingOrder(float $quantity = 2): Order
    {
        $this->actingAs($this->customer);
        $cartService = app(CartService::class);
        $cartService->clear();
        $cartService->add($this->product, $quantity);

        $orderService = app(OrderService::class);

        return $orderService->createOrder($this->customer, [
            'fulfilment_method' => 'pickup',
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'phone' => '654321987',
        ]);
    }

    public function test_customer_can_render_payment_screen(): void
    {
        $order = $this->createPendingOrder(2);

        $response = $this->actingAs($this->customer)
            ->get(route('payment.show', $order->id));

        $response->assertStatus(200);
        $response->assertSee($order->number);
        $response->assertSee('9,00 €');
    }

    public function test_successful_mock_payment_confirms_order_and_consumes_reservation(): void
    {
        $order = $this->createPendingOrder(2);

        $stock = StockLevel::where('location_id', $this->store->id)
            ->where('product_id', $this->product->id)
            ->first();

        // 2 units reserved, 20 physical
        $this->assertEquals(2.00, (float) $stock->reserved);
        $this->assertEquals(20.00, (float) $stock->quantity);

        $response = $this->actingAs($this->customer)
            ->post(route('payment.mock', $order->id), [
                'card_holder' => 'Maria Garcia',
                'card_number' => '4242424242424242',
                'expiry' => '12/28',
                'cvv' => '123',
                'simulate_outcome' => 'success',
            ]);

        $response->assertRedirect(route('account.orders.show', $order->id));
        $response->assertSessionHas('success');

        $order->refresh();
        $stock->refresh();

        $this->assertTrue($order->isPaid());
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->status);
        $this->assertNotNull($order->paid_at);

        // Verify stock reservation was consumed (quantity decreased, reserved decreased)
        $this->assertEquals(0.00, (float) $stock->reserved);
        $this->assertEquals(18.00, (float) $stock->quantity);

        // Payment record created
        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(Payment::STATUS_COMPLETED, $payment->status);
        $this->assertEquals('4242', $payment->card_last4);
    }

    public function test_failed_mock_payment_keeps_order_in_pending_payment(): void
    {
        $order = $this->createPendingOrder(2);

        $response = $this->actingAs($this->customer)
            ->post(route('payment.mock', $order->id), [
                'simulate_outcome' => 'failure',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertFalse($order->isPaid());
        $this->assertEquals(Order::STATUS_PENDING_PAYMENT, $order->status);
        $this->assertEquals(Order::PAYMENT_FAILED, $order->payment_status);

        // Payment record logged as failed
        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(Payment::STATUS_FAILED, $payment->status);
    }

    public function test_stripe_webhook_succeeded_event_confirms_order_idempotently(): void
    {
        $order = $this->createPendingOrder(3);

        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123456789',
                    'metadata' => [
                        'order_id' => $order->id,
                    ],
                    'charges' => [
                        'data' => [
                            [
                                'payment_method_details' => [
                                    'card' => [
                                        'last4' => '4242',
                                        'brand' => 'visa',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // First delivery of webhook
        $response1 = $this->postJson(route('webhooks.stripe'), $payload);
        $response1->assertStatus(200);

        $order->refresh();
        $this->assertTrue($order->isPaid());
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->status);

        $stock = StockLevel::where('location_id', $this->store->id)->where('product_id', $this->product->id)->first();
        $this->assertEquals(17.00, (float) $stock->quantity);
        $this->assertEquals(0.00, (float) $stock->reserved);

        // Duplicate delivery (idempotency check)
        $response2 = $this->postJson(route('webhooks.stripe'), $payload);
        $response2->assertStatus(200);

        // Stock quantity should not be double deducted
        $stock->refresh();
        $this->assertEquals(17.00, (float) $stock->quantity);
    }

    public function test_artisan_expire_unpaid_command_cancels_stale_orders_and_releases_stock(): void
    {
        $order = $this->createPendingOrder(4);

        $stock = StockLevel::where('location_id', $this->store->id)->where('product_id', $this->product->id)->first();
        $this->assertEquals(4.00, (float) $stock->reserved);
        $this->assertEquals(20.00, (float) $stock->quantity);

        // Backdate order to 40 minutes ago (beyond default 30 min timeout)
        $order->timestamps = false;
        $order->created_at = now()->subMinutes(40);
        $order->save();

        $this->artisan('orders:expire-unpaid', ['--minutes' => 30])
            ->expectsOutputToContain('Se han cancelado 1 pedidos expirados y se ha liberado el stock reservado.')
            ->assertExitCode(0);

        $order->refresh();
        $stock->refresh();

        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);
        $this->assertNotNull($order->cancelled_at);

        // Reserved stock returned to 0, physical quantity untouched
        $this->assertEquals(0.00, (float) $stock->reserved);
        $this->assertEquals(20.00, (float) $stock->quantity);
    }
}
