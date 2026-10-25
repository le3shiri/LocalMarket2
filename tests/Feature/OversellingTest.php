<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OversellingTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Product $oliveOil;

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
            'name' => 'Aceites',
            'slug' => 'aceites',
            'is_active' => true,
        ]);

        $this->oliveOil = Product::create([
            'category_id' => $category->id,
            'name' => 'Aceite Virgen Extra 1L',
            'slug' => 'aceite-virgen-extra-1l',
            'description' => 'Aceite de oliva virgen extra',
            'price' => 8.50,
            'sale_unit' => Product::UNIT_PIECE,
            'is_active' => true,
        ]);

        // Only 5 units in stock, 3 already reserved -> 2 available
        StockLevel::create([
            'location_id' => $this->store->id,
            'product_id' => $this->oliveOil->id,
            'quantity' => 5.00,
            'reserved' => 3.00,
        ]);

        $this->customer = User::factory()->create([
            'role' => User::CUSTOMER,
            'is_active' => true,
        ]);
    }

    public function test_cannot_add_more_than_available_stock_to_cart(): void
    {
        // Available = 5 - 3 = 2 units
        $response = $this->post(route('cart.add'), [
            'product_id' => $this->oliveOil->id,
            'quantity' => 3, // exceeds 2
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEmpty(session('cart', []));
    }

    public function test_checkout_fails_atomically_if_stock_becomes_insufficient(): void
    {
        $this->actingAs($this->customer);

        // Put 2 units into cart (allowed since 2 are available)
        $this->post(route('cart.add'), [
            'product_id' => $this->oliveOil->id,
            'quantity' => 2,
        ]);

        // Another cashier/process simultaneously sells/reserves remaining 2 units
        $stock = StockLevel::where('location_id', $this->store->id)
            ->where('product_id', $this->oliveOil->id)
            ->first();
        $stock->update(['reserved' => 5.00]); // 5 quantity, 5 reserved -> 0 available!

        // Customer attempts to checkout
        $response = $this->post(route('checkout.store'), [
            'fulfilment_method' => 'pickup',
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'phone' => '654321987',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Order was NOT created
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);

        // Stock reserved did not get incremented or corrupted
        $stock->refresh();
        $this->assertEquals(5.00, (float) $stock->reserved);
    }
}
