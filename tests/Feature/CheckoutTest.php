<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Product $banana;

    private Product $milk;

    private Location $store;

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
            'name' => 'Alimentación',
            'slug' => 'alimentacion',
            'is_active' => true,
        ]);

        $this->banana = Product::create([
            'category_id' => $category->id,
            'name' => 'Plátano de Canarias',
            'slug' => 'platano-de-canarias',
            'description' => 'Plátanos frescos',
            'price' => 2.50,
            'sale_unit' => Product::UNIT_KG,
            'is_active' => true,
        ]);

        $this->milk = Product::create([
            'category_id' => $category->id,
            'name' => 'Leche Entera',
            'slug' => 'leche-entera',
            'description' => 'Brick de 1L',
            'price' => 1.20,
            'sale_unit' => Product::UNIT_PIECE,
            'is_active' => true,
        ]);

        // Stock in store
        StockLevel::create([
            'location_id' => $this->store->id,
            'product_id' => $this->banana->id,
            'quantity' => 50.00,
            'reserved' => 0.00,
        ]);

        StockLevel::create([
            'location_id' => $this->store->id,
            'product_id' => $this->milk->id,
            'quantity' => 100.00,
            'reserved' => 0.00,
        ]);

        $this->customer = User::factory()->create([
            'role' => User::CUSTOMER,
            'is_active' => true,
        ]);
    }

    public function test_can_add_item_to_cart_and_view_cart(): void
    {
        $response = $this->post(route('cart.add'), [
            'product_id' => $this->milk->id,
            'quantity' => 3,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->get(route('cart.index'))
            ->assertStatus(200)
            ->assertSee('Leche Entera')
            ->assertSee('3,60 €');
    }

    public function test_checkout_requires_authenticated_user(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_checkout_validates_delivery_postcode_within_barcelona_range(): void
    {
        $this->actingAs($this->customer);

        // Put item in cart
        $this->post(route('cart.add'), [
            'product_id' => $this->milk->id,
            'quantity' => 2,
        ]);

        // Madrid postcode (invalid for Barcelona local market delivery)
        $response = $this->post(route('checkout.store'), [
            'fulfilment_method' => 'delivery',
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'phone' => '600111222',
            'address' => 'Gran Vía 12',
            'postcode' => '28013',
        ]);

        $response->assertSessionHasErrors(['postcode']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_successful_checkout_creates_order_and_reserves_stock(): void
    {
        $this->actingAs($this->customer);

        $this->post(route('cart.add'), [
            'product_id' => $this->banana->id,
            'quantity' => 2.5,
        ]);

        $this->post(route('cart.add'), [
            'product_id' => $this->milk->id,
            'quantity' => 4,
        ]);

        // Valid Barcelona postcode
        $response = $this->post(route('checkout.store'), [
            'fulfilment_method' => 'delivery',
            'customer_name' => 'Maria Garcia',
            'customer_email' => 'maria@example.com',
            'phone' => '654987321',
            'address' => 'Carrer de Mallorca 123, 2º 1ª',
            'postcode' => '08036',
            'notes' => 'Por favor llamar al timbre A',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('account.orders.show', $order->id));

        $this->assertEquals(Order::STATUS_PENDING_PAYMENT, $order->status);
        $this->assertEquals('delivery', $order->fulfilment_method);
        $this->assertEquals('08036', $order->postcode);
        $this->assertEquals(2, $order->items()->count());

        // Delivery fee applied (order below 50 EUR free delivery threshold: 4.95 default)
        $this->assertEquals(4.95, (float) $order->delivery_fee);

        // Verify stock is reserved
        $bananaStock = StockLevel::where('location_id', $this->store->id)
            ->where('product_id', $this->banana->id)
            ->first();
        $milkStock = StockLevel::where('location_id', $this->store->id)
            ->where('product_id', $this->milk->id)
            ->first();

        $this->assertEquals(2.5, (float) $bananaStock->reserved);
        $this->assertEquals(4.0, (float) $milkStock->reserved);

        // Cart is cleared
        $this->assertEmpty(session('cart', []));
    }

    public function test_free_delivery_applied_when_subtotal_reaches_fifty_euros(): void
    {
        $this->actingAs($this->customer);

        // Add 50 milk units = 60.00 EUR
        $this->post(route('cart.add'), [
            'product_id' => $this->milk->id,
            'quantity' => 50,
        ]);

        $response = $this->post(route('checkout.store'), [
            'fulfilment_method' => 'delivery',
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'phone' => '612345678',
            'address' => 'Passeig de Gràcia 50',
            'postcode' => '08007',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(0.00, (float) $order->delivery_fee);
    }
}
