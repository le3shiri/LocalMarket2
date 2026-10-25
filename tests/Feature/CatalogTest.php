<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_categories_and_featured_products(): void
    {
        $category = Category::factory()->create(['name' => 'Frutería', 'is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Naranjas de Valencia',
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Frutería');
        $response->assertSee('Naranjas de Valencia');
    }

    public function test_customer_can_browse_catalogue(): void
    {
        $category = Category::factory()->create(['name' => 'Panadería', 'is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Barra de Pan Artesana',
            'is_active' => true,
        ]);

        $response = $this->get(route('catalog.index'));

        $response->assertStatus(200);
        $response->assertSee('Panadería');
        $response->assertSee('Barra de Pan Artesana');
    }

    public function test_customer_can_filter_catalogue_by_category(): void
    {
        $cat1 = Category::factory()->create(['name' => 'Lácteos', 'slug' => 'lacteos', 'is_active' => true]);
        $cat2 = Category::factory()->create(['name' => 'Carnes', 'slug' => 'carnes', 'is_active' => true]);

        $prod1 = Product::factory()->create(['category_id' => $cat1->id, 'name' => 'Yogur Natural']);
        $prod2 = Product::factory()->create(['category_id' => $cat2->id, 'name' => 'Filete de Ternera']);

        $response = $this->get(route('catalog.index', ['categoria' => 'lacteos']));

        $response->assertStatus(200);
        $response->assertSee('Yogur Natural');
        $response->assertDontSee('Filete de Ternera');
    }

    public function test_customer_can_search_products_by_name(): void
    {
        $product1 = Product::factory()->create(['name' => 'Aceite de Oliva']);
        $product2 = Product::factory()->create(['name' => 'Arroz Bomba']);

        $response = $this->get(route('catalog.index', ['q' => 'Oliva']));

        $response->assertStatus(200);
        $response->assertSee('Aceite de Oliva');
        $response->assertDontSee('Arroz Bomba');
    }

    public function test_customer_can_view_product_details(): void
    {
        $product = Product::factory()->create([
            'name' => 'Queso Manchego Curado',
            'description' => 'Queso madurado en cueva 6 meses.',
            'price' => 12.50,
            'is_active' => true,
        ]);

        $response = $this->get(route('catalog.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('Queso Manchego Curado');
        $response->assertSee('Queso madurado en cueva 6 meses.');
        $response->assertSee('12,50 €');
    }

    public function test_inactive_products_are_not_shown_or_accessible(): void
    {
        $product = Product::factory()->create([
            'name' => 'Producto Oculto',
            'is_active' => false,
        ]);

        $catalogResponse = $this->get(route('catalog.index'));
        $catalogResponse->assertDontSee('Producto Oculto');

        $detailResponse = $this->get(route('catalog.show', $product->slug));
        $detailResponse->assertStatus(404);
    }
}
