<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Iniciar sesión');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'juan@localmarket.test',
            'password' => Hash::make('password123'),
            'role' => User::CUSTOMER,
            'is_active' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'juan@localmarket.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home'));
    }

    public function test_staff_users_are_redirected_to_staff_dashboard_after_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@localmarket.test',
            'password' => Hash::make('password123'),
            'role' => User::ADMIN,
            'is_active' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'admin@localmarket.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('staff.dashboard'));
    }

    public function test_deactivated_users_cannot_authenticate(): void
    {
        User::factory()->create([
            'email' => 'desactivado@localmarket.test',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'desactivado@localmarket.test',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('Crear cuenta');
    }

    public function test_new_customers_can_register_and_receive_customer_role(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Nuevo Cliente',
            'email' => 'nuevo@localmarket.test',
            'phone' => '654987321',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home'));

        $user = User::where('email', 'nuevo@localmarket.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals(User::CUSTOMER, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@localmarket.test',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'reset@localmarket.test',
        ]);

        $response->assertSessionHas('success');
    }
}
