<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\User;
use App\Services\CartService;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $otherCustomer;

    private User $admin;

    private Location $store;

    private Product $bread;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Location::create([
            'name' => 'Tienda Principal',
            'type' => Location::TYPE_STORE,
            'address' => 'Carrer de Mallorca 100, Barcelona',
            'is_online_source' => true,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Panadería',
            'slug' => 'panaderia',
            'is_active' => true,
        ]);

        $this->bread = Product::create([
            'category_id' => $category->id,
            'name' => 'Barra Artesana',
            'slug' => 'barra-artesana',
            'description' => 'Pan artesano del día',
            'price' => 1.10,
            'sale_unit' => Product::UNIT_PIECE,
            'is_active' => true,
        ]);

        StockLevel::create([
            'location_id' => $this->store->id,
            'product_id' => $this->bread->id,
            'quantity' => 20.00,
            'reserved' => 0.00,
        ]);

        $this->customer = User::factory()->create([
            'role' => User::CUSTOMER,
            'is_active' => true,
        ]);

        $this->otherCustomer = User::factory()->create([
            'role' => User::CUSTOMER,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => User::ADMIN,
            'is_active' => true,
        ]);
    }

    private function createCustomerOrder(User $customer, float $quantity = 2): Order
    {
        $this->actingAs($customer);
        $cartService = app(CartService::class);
        $cartService->clear();
        $cartService->add($this->bread, $quantity);

        $orderService = app(OrderService::class);

        return $orderService->createOrder($customer, [
            'fulfilment_method' => 'pickup',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'phone' => '600123456',
        ]);
    }

    public function test_customer_can_only_view_own_orders(): void
    {
        $order = $this->createCustomerOrder($this->customer, 2);

        // Own customer can view
        $this->actingAs($this->customer)
            ->get(route('account.orders.show', $order->id))
            ->assertStatus(200)
            ->assertSee($order->number);

        // Other customer gets 404/forbidden
        $this->actingAs($this->otherCustomer)
            ->get(route('account.orders.show', $order->id))
            ->assertStatus(404);
    }

    public function test_customer_can_cancel_pending_order_and_reserved_stock_is_released(): void
    {
        $order = $this->createCustomerOrder($this->customer, 5);

        $stock = StockLevel::where('location_id', $this->store->id)->where('product_id', $this->bread->id)->first();
        $this->assertEquals(5.00, (float) $stock->reserved);

        $this->actingAs($this->customer)
            ->post(route('account.orders.cancel', $order->id))
            ->assertRedirect();

        $order->refresh();
        $stock->refresh();

        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);
        $this->assertEquals(0.00, (float) $stock->reserved);
        $this->assertEquals(20.00, (float) $stock->quantity);
    }

    public function test_staff_can_transition_order_status_following_workflow(): void
    {
        $order = $this->createCustomerOrder($this->customer, 2);

        $this->actingAs($this->admin);

        // Transition pending_payment -> confirmed
        $response = $this->post(route('staff.orders.status', $order), [
            'status' => Order::STATUS_CONFIRMED,
            'note' => 'Cobro verificado',
        ]);
        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->status);

        // Transition confirmed -> preparing
        $this->post(route('staff.orders.status', $order), [
            'status' => Order::STATUS_PREPARING,
            'note' => 'Embalando pedido',
        ]);
        $order->refresh();
        $this->assertEquals(Order::STATUS_PREPARING, $order->status);

        // Transition preparing -> ready_for_pickup
        $this->post(route('staff.orders.status', $order), [
            'status' => Order::STATUS_READY_FOR_PICKUP,
            'note' => 'Listo en mostrador',
        ]);
        $order->refresh();
        $this->assertEquals(Order::STATUS_READY_FOR_PICKUP, $order->status);

        // Invalid transition: cannot jump from ready_for_pickup to out_for_delivery on a pickup order
        $this->post(route('staff.orders.status', $order), [
            'status' => Order::STATUS_OUT_FOR_DELIVERY,
        ]);
        $order->refresh();
        $this->assertEquals(Order::STATUS_READY_FOR_PICKUP, $order->status);

        // Transition ready_for_pickup -> completed
        $this->post(route('staff.orders.status', $order), [
            'status' => Order::STATUS_COMPLETED,
            'note' => 'Entregado al cliente',
        ]);
        $order->refresh();
        $this->assertEquals(Order::STATUS_COMPLETED, $order->status);

        // Status history recorded
        $this->assertGreaterThanOrEqual(4, $order->statusHistories()->count());
    }

    public function test_cancelling_paid_order_returns_physical_stock(): void
    {
        $order = $this->createCustomerOrder($this->customer, 4);

        $orderService = app(OrderService::class);
        $inventoryService = app(InventoryService::class);

        // Payment confirmed: reservation consumed
        $inventoryService->consumeReservation($order, $this->store);
        $order->update(['payment_status' => Order::PAYMENT_PAID, 'status' => Order::STATUS_CONFIRMED]);

        $stock = StockLevel::where('location_id', $this->store->id)->where('product_id', $this->bread->id)->first();
        $this->assertEquals(16.00, (float) $stock->quantity);
        $this->assertEquals(0.00, (float) $stock->reserved);

        // Admin cancels this paid order -> physical stock must be returned (+4)
        $orderService->changeStatus($order, Order::STATUS_CANCELLED, $this->admin, 'Cliente desistió');

        $stock->refresh();
        $this->assertEquals(20.00, (float) $stock->quantity);
        $this->assertEquals(0.00, (float) $stock->reserved);
    }
}
