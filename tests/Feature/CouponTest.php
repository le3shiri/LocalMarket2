<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\User;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $admin;

    private Product $cheese;

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
            'name' => 'Quesos',
            'slug' => 'quesos',
            'is_active' => true,
        ]);

        $this->cheese = Product::create([
            'category_id' => $category->id,
            'name' => 'Queso Manchego Curado',
            'slug' => 'queso-manchego-curado',
            'price' => 12.00,
            'sale_unit' => Product::UNIT_PIECE,
            'is_active' => true,
        ]);

        StockLevel::create([
            'location_id' => $this->store->id,
            'product_id' => $this->cheese->id,
            'quantity' => 20.00,
            'reserved' => 0.00,
        ]);

        $this->customer = User::factory()->create([
            'role' => User::CUSTOMER,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => User::ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_and_manage_coupons(): void
    {
        $response = $this->actingAs($this->admin)->post(route('staff.coupons.store'), [
            'code' => 'VERANO15',
            'description' => '15% de descuento',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 15.00,
            'min_order_amount' => 20.00,
            'max_uses' => 100,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('staff.coupons.index'));
        $this->assertDatabaseHas('coupons', [
            'code' => 'VERANO15',
            'type' => 'percentage',
            'value' => 15.00,
            'min_order_amount' => 20.00,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_apply_valid_percentage_coupon_at_checkout(): void
    {
        $coupon = Coupon::create([
            'code' => 'DESC10',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 10.00, // 10%
            'min_order_amount' => 20.00,
            'is_active' => true,
        ]);

        $this->actingAs($this->customer);
        $cartService = app(CartService::class);
        $cartService->clear();
        $cartService->add($this->cheese, 2); // 2 * 12 = 24.00 EUR (meets 20 EUR min)

        $response = $this->post(route('checkout.coupon.apply'), [
            'code' => 'desc10', // case-insensitive test
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('DESC10', session(CouponService::SESSION_KEY));

        // Subtotal = 24.00 EUR, 10% discount = 2.40 EUR
        $orderService = app(OrderService::class);
        $order = $orderService->createOrder($this->customer, [
            'fulfilment_method' => 'pickup',
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'phone' => '654321987',
        ]);

        $this->assertEquals(24.00, (float) $order->subtotal);
        $this->assertEquals(2.40, (float) $order->discount);
        $this->assertEquals(21.60, (float) $order->total);
        $this->assertEquals('DESC10', $order->coupon_code);

        // Coupon usage incremented
        $coupon->refresh();
        $this->assertEquals(1, $coupon->uses_count);
    }

    public function test_coupon_is_rejected_if_subtotal_is_below_minimum_order_amount(): void
    {
        Coupon::create([
            'code' => 'GRANCOMPRA',
            'type' => Coupon::TYPE_FIXED,
            'value' => 5.00,
            'min_order_amount' => 50.00,
            'is_active' => true,
        ]);

        $this->actingAs($this->customer);
        $cartService = app(CartService::class);
        $cartService->clear();
        $cartService->add($this->cheese, 1); // 12.00 EUR < 50.00 EUR

        $response = $this->post(route('checkout.coupon.apply'), [
            'code' => 'GRANCOMPRA',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNull(session(CouponService::SESSION_KEY));
    }

    public function test_expired_coupon_cannot_be_applied(): void
    {
        Coupon::create([
            'code' => 'CADUCADO',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 20.00,
            'min_order_amount' => 0.00,
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->actingAs($this->customer);
        $cartService = app(CartService::class);
        $cartService->clear();
        $cartService->add($this->cheese, 1);

        $response = $this->post(route('checkout.coupon.apply'), [
            'code' => 'CADUCADO',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNull(session(CouponService::SESSION_KEY));
    }
}
