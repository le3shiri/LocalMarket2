<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => User::ADMIN]);
    }

    public function test_admin_can_view_categories_list(): void
    {
        Category::factory()->create(['name' => 'Pescados y Mariscos']);

        $response = $this->actingAs($this->admin)->get(route('staff.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Pescados y Mariscos');
    }

    public function test_admin_can_create_a_category_and_it_is_audited(): void
    {
        $response = $this->actingAs($this->admin)->post(route('staff.categories.store'), [
            'name' => 'Bebidas y Zumos',
            'icon' => '🧃',
            'description' => 'Zumos naturales y aguas minerales.',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('staff.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Bebidas y Zumos', 'icon' => '🧃']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'category.created',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_view_products_list(): void
    {
        $product = Product::factory()->create(['name' => 'Lentejas Pardinas']);

        $response = $this->actingAs($this->admin)->get(route('staff.products.index'));

        $response->assertStatus(200);
        $response->assertSee('Lentejas Pardinas');
    }

    public function test_admin_can_create_a_product_with_validation_and_audit(): void
    {
        $category = Category::factory()->create(['name' => 'Pastas y Legumbres']);

        $response = $this->actingAs($this->admin)->post(route('staff.products.store'), [
            'category_id' => $category->id,
            'name' => 'Garbanzo Pedrosillano 1kg',
            'price' => 2.30,
            'sale_unit' => Product::UNIT_PACK,
            'low_stock_threshold' => 10,
            'shelf_location' => 'Pasillo 3, Nivel 2',
            'description' => 'Garbanzos selectos de cocción rápida.',
            'tracks_expiry' => 0,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('staff.products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Garbanzo Pedrosillano 1kg',
            'price' => 2.30,
            'sale_unit' => Product::UNIT_PACK,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'product.created',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Nombre Antiguo', 'price' => 1.00]);

        $response = $this->actingAs($this->admin)->put(route('staff.products.update', $product), [
            'category_id' => $product->category_id,
            'name' => 'Nombre Actualizado',
            'price' => 1.50,
            'sale_unit' => $product->sale_unit,
            'low_stock_threshold' => 5,
            'is_active' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('staff.products.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Nombre Actualizado', 'price' => 1.50]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'product.updated',
            'user_id' => $this->admin->id,
        ]);
    }
}
