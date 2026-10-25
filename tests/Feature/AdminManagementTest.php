<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ADMIN,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'name' => 'Carlos Cajero',
            'email' => 'carlos@localmarket.test',
            'role' => User::CASHIER,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_users_list_and_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('staff.users.index', ['role' => 'cashier']));

        $response->assertStatus(200);
        $response->assertSee('Carlos Cajero');
        $response->assertSee('carlos@localmarket.test');
    }

    public function test_admin_can_create_new_staff_member(): void
    {
        $response = $this->actingAs($this->admin)->post(route('staff.users.store'), [
            'name' => 'Elena Almacén',
            'email' => 'elena@localmarket.test',
            'role' => User::WAREHOUSE,
            'phone' => '611222333',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('staff.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'elena@localmarket.test',
            'role' => 'warehouse',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.created',
        ]);
    }

    public function test_admin_can_toggle_user_active_status(): void
    {
        $this->assertTrue($this->cashier->is_active);

        // Deactivate cashier
        $response = $this->actingAs($this->admin)
            ->post(route('staff.users.toggle', $this->cashier));

        $response->assertRedirect();
        $this->cashier->refresh();
        $this->assertFalse($this->cashier->is_active);

        // Log out admin before attempting login as cashier
        auth()->logout();

        // Deactivated cashier can no longer log in
        $loginAttempt = $this->post(route('login'), [
            'email' => $this->cashier->email,
            'password' => 'password',
        ]);
        $this->assertGuest();

        // Reactivate cashier
        $this->actingAs($this->admin)
            ->post(route('staff.users.toggle', $this->cashier));
        $this->cashier->refresh();
        $this->assertTrue($this->cashier->is_active);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('staff.users.toggle', $this->admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->admin->refresh();
        $this->assertTrue($this->admin->is_active);
    }

    public function test_admin_can_inspect_audit_logs(): void
    {
        AuditLog::record('test.action', null, ['foo' => 'bar']);

        $response = $this->actingAs($this->admin)->get(route('staff.audit.index'));

        $response->assertStatus(200);
        $response->assertSee('test.action');
    }
}
