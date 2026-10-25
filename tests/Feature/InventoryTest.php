<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $warehouseUser;

    private User $cashierUser;

    private Location $warehouse;

    private Location $store;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouseUser = User::factory()->create(['role' => User::WAREHOUSE]);
        $this->cashierUser = User::factory()->create(['role' => User::CASHIER]);

        $this->warehouse = Location::factory()->warehouse()->create();
        $this->store = Location::factory()->onlineSource()->create();

        $this->product = Product::factory()->create([
            'name' => 'Manzanas Golden',
            'sale_unit' => Product::UNIT_KG,
            'tracks_expiry' => false,
            'low_stock_threshold' => 5,
        ]);
    }

    public function test_warehouse_user_can_record_stock_receipt(): void
    {
        $response = $this->actingAs($this->warehouseUser)->post(route('staff.inventory.receipt'), [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 50.0,
            'reason' => 'Recepción camión semanal',
        ]);

        $response->assertRedirect(route('staff.inventory.index', ['location_id' => $this->warehouse->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stock_levels', [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 50.0,
            'reserved' => 0.0,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'type' => StockMovement::TYPE_RECEIPT,
            'quantity' => 50.0,
            'user_id' => $this->warehouseUser->id,
        ]);
    }

    public function test_perishable_product_receipt_requires_lot_and_expiry_and_creates_batch(): void
    {
        $perishable = Product::factory()->create([
            'name' => 'Leche Fresca 1L',
            'tracks_expiry' => true,
        ]);

        // Fails when lot and expiry are missing
        $failResponse = $this->actingAs($this->warehouseUser)->post(route('staff.inventory.receipt'), [
            'product_id' => $perishable->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 20.0,
            'reason' => 'Entrada lácteos',
        ]);

        $failResponse->assertSessionHas('error');

        // Succeeds with lot and expiry
        $successResponse = $this->actingAs($this->warehouseUser)->post(route('staff.inventory.receipt'), [
            'product_id' => $perishable->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 20.0,
            'reason' => 'Entrada lácteos con lote',
            'lot_code' => 'LOT-2026-X1',
            'expiry_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $successResponse->assertSessionHas('success');

        $this->assertDatabaseHas('batches', [
            'product_id' => $perishable->id,
            'location_id' => $this->warehouse->id,
            'lot_code' => 'LOT-2026-X1',
            'quantity' => 20.0,
        ]);
    }

    public function test_warehouse_user_can_transfer_stock_between_locations(): void
    {
        // Seed initial warehouse stock
        StockLevel::create([
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 30.0,
            'reserved' => 0.0,
        ]);

        $response = $this->actingAs($this->warehouseUser)->post(route('staff.inventory.transfer'), [
            'product_id' => $this->product->id,
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $this->store->id,
            'quantity' => 10.0,
            'reason' => 'Reposición de tienda',
        ]);

        $response->assertRedirect(route('staff.inventory.index', ['location_id' => $this->store->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stock_levels', [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 20.0,
        ]);

        $this->assertDatabaseHas('stock_levels', [
            'product_id' => $this->product->id,
            'location_id' => $this->store->id,
            'quantity' => 10.0,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'type' => StockMovement::TYPE_TRANSFER_OUT,
            'quantity' => -10.0,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->store->id,
            'type' => StockMovement::TYPE_TRANSFER_IN,
            'quantity' => 10.0,
        ]);
    }

    public function test_transfer_fails_when_unreserved_stock_is_insufficient(): void
    {
        StockLevel::create([
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 5.0,
            'reserved' => 0.0,
        ]);

        $response = $this->actingAs($this->warehouseUser)->post(route('staff.inventory.transfer'), [
            'product_id' => $this->product->id,
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $this->store->id,
            'quantity' => 10.0, // Exceeds available 5
            'reason' => 'Traspaso excesivo',
        ]);

        $response->assertSessionHas('error');

        // Unchanged
        $this->assertDatabaseHas('stock_levels', [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 5.0,
        ]);
    }

    public function test_warehouse_user_can_record_stock_adjustment(): void
    {
        StockLevel::create([
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 15.0,
            'reserved' => 0.0,
        ]);

        $response = $this->actingAs($this->warehouseUser)->post(route('staff.inventory.adjustment'), [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity_delta' => -2.0,
            'reason' => 'Rotura accidental en almacén',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stock_levels', [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 13.0,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_ADJUSTMENT,
            'quantity' => -2.0,
            'reason' => 'Rotura accidental en almacén',
        ]);
    }

    public function test_adjustment_cannot_make_stock_negative(): void
    {
        StockLevel::create([
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 5.0,
            'reserved' => 0.0,
        ]);

        $response = $this->actingAs($this->warehouseUser)->post(route('staff.inventory.adjustment'), [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity_delta' => -10.0,
            'reason' => 'Ajuste excesivo',
        ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('stock_levels', [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 5.0,
        ]);
    }

    public function test_cashier_can_record_in_store_sale_and_it_deducts_store_stock(): void
    {
        StockLevel::create([
            'product_id' => $this->product->id,
            'location_id' => $this->store->id,
            'quantity' => 10.0,
            'reserved' => 0.0,
        ]);

        $response = $this->actingAs($this->cashierUser)->post(route('staff.pos.store'), [
            'location_id' => $this->store->id,
            'product_id' => $this->product->id,
            'quantity' => 2.5,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stock_levels', [
            'product_id' => $this->product->id,
            'location_id' => $this->store->id,
            'quantity' => 7.5,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->store->id,
            'type' => StockMovement::TYPE_STORE_SALE,
            'quantity' => -2.5,
            'user_id' => $this->cashierUser->id,
        ]);
    }

    public function test_cashier_cannot_record_sale_in_warehouse_location(): void
    {
        $response = $this->actingAs($this->cashierUser)->post(route('staff.pos.store'), [
            'location_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 1.0,
        ]);

        $response->assertSessionHas('error');
    }
}
