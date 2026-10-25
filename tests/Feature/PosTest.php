<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Location $store;

    protected Product $apple;

    protected Product $milk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'role' => User::CASHIER,
            'is_active' => true,
        ]);

        $this->store = Location::create([
            'name' => 'Tienda Mercat Barcelona',
            'type' => Location::TYPE_STORE,
            'address' => 'Carrer del Mercat, 12, 08001 Barcelona',
            'is_active' => true,
            'is_online_source' => true,
        ]);

        $category = Category::create([
            'name' => 'Frutas y Verduras',
            'slug' => 'frutas-y-verduras',
            'is_active' => true,
        ]);

        $this->apple = Product::create([
            'category_id' => $category->id,
            'name' => 'Manzana Golden de Lleida',
            'slug' => 'manzana-golden-lleida',
            'price' => 2.20,
            'sale_unit' => 'kg',
            'is_active' => true,
        ]);

        $this->milk = Product::create([
            'category_id' => $category->id,
            'name' => 'Leche Entera 1L',
            'slug' => 'leche-entera-1l',
            'price' => 1.15,
            'sale_unit' => 'unit',
            'is_active' => true,
        ]);

        StockLevel::create([
            'location_id' => $this->store->id,
            'product_id' => $this->apple->id,
            'quantity' => 25.0,
            'reserved' => 0.0,
        ]);

        StockLevel::create([
            'location_id' => $this->store->id,
            'product_id' => $this->milk->id,
            'quantity' => 40.0,
            'reserved' => 0.0,
        ]);
    }

    public function test_cashier_can_render_pos_screen(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('staff.pos.create'));

        $response->assertStatus(200);
        $response->assertSee('Manzana Golden de Lleida');
        $response->assertSee('Tienda Mercat Barcelona');
        $response->assertSee('Ticket en Curso');
    }

    public function test_cashier_can_process_multi_item_pos_sale(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('staff.pos.store'), [
            'location_id' => $this->store->id,
            'payment_method' => 'cash',
            'tendered_amount' => 10.00,
            'items' => [
                [
                    'product_id' => $this->apple->id,
                    'quantity' => 2.0,
                ],
                [
                    'product_id' => $this->milk->id,
                    'quantity' => 3.0,
                ],
            ],
        ]);

        $response->assertRedirect(route('staff.pos.create'));
        $response->assertSessionHas('success');

        // Stock deducted accurately
        $this->assertDatabaseHas('stock_levels', [
            'location_id' => $this->store->id,
            'product_id' => $this->apple->id,
            'quantity' => 23.0,
        ]);

        $this->assertDatabaseHas('stock_levels', [
            'location_id' => $this->store->id,
            'product_id' => $this->milk->id,
            'quantity' => 37.0,
        ]);

        // Stock movements recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->apple->id,
            'location_id' => $this->store->id,
            'type' => StockMovement::TYPE_STORE_SALE,
            'quantity' => -2.0,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->milk->id,
            'location_id' => $this->store->id,
            'type' => StockMovement::TYPE_STORE_SALE,
            'quantity' => -3.0,
        ]);
    }
}
