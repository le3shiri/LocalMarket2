<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 0.5, 30),
            'sale_unit' => fake()->randomElement([Product::UNIT_PIECE, Product::UNIT_KG, Product::UNIT_PACK]),
            'shelf_location' => 'Pasillo '.fake()->numberBetween(1, 10),
            'tracks_expiry' => false,
            'low_stock_threshold' => 5.0,
            'is_active' => true,
        ];
    }
}
