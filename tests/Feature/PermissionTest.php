<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_when_accessing_staff_dashboard(): void
    {
        $response = $this->get(route('staff.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_customers_receive_403_forbidden_on_staff_dashboard(): void
    {
        $customer = User::factory()->create(['role' => User::CUSTOMER]);

        $response = $this->actingAs($customer)->get(route('staff.dashboard'));
        $response->assertStatus(403);
    }

    public function test_warehouse_technician_can_access_inventory_but_not_admin_pages(): void
    {
        $warehouse = User::factory()->create(['role' => User::WAREHOUSE]);

        // Authorized
        $response = $this->actingAs($warehouse)->get(route('staff.inventory.index'));
        $response->assertStatus(200);

        // Forbidden
        $response = $this->actingAs($warehouse)->get(route('staff.products.index'));
        $response->assertStatus(403);
    }

    public function test_cashier_can_access_pos_but_not_inventory_management(): void
    {
        $cashier = User::factory()->create(['role' => User::CASHIER]);

        // Authorized
        $response = $this->actingAs($cashier)->get(route('staff.pos.create'));
        $response->assertStatus(200);

        // Forbidden
        $response = $this->actingAs($cashier)->get(route('staff.inventory.index'));
        $response->assertStatus(403);
    }

    public function test_admin_has_access_to_all_staff_sections(): void
    {
        $admin = User::factory()->create(['role' => User::ADMIN]);

        $this->actingAs($admin)->get(route('staff.dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('staff.inventory.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('staff.pos.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('staff.products.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('staff.categories.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('staff.coupons.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('staff.users.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('staff.audit.index'))->assertStatus(200);
    }
}
