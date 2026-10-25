<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users for each role
        User::firstOrCreate(
            ['email' => 'admin@localmarket.test'],
            [
                'name' => 'Administrador LocalMarket',
                'password' => Hash::make('password123'),
                'role' => User::ADMIN,
                'phone' => '931234567',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'almacen@localmarket.test'],
            [
                'name' => 'Técnico Almacén',
                'password' => Hash::make('password123'),
                'role' => User::WAREHOUSE,
                'phone' => '931234568',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cajero@localmarket.test'],
            [
                'name' => 'Cajero Tienda',
                'password' => Hash::make('password123'),
                'role' => User::CASHIER,
                'phone' => '931234569',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cliente@localmarket.test'],
            [
                'name' => 'Cliente Demostración',
                'password' => Hash::make('password123'),
                'role' => User::CUSTOMER,
                'phone' => '612345678',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'desactivado@localmarket.test'],
            [
                'name' => 'Usuario Desactivado',
                'password' => Hash::make('password123'),
                'role' => User::CUSTOMER,
                'phone' => '600000000',
                'is_active' => false,
            ]
        );

        // 2. Categories
        $categoriesData = [
            ['name' => 'Frutas y Verduras', 'icon' => '🍎', 'description' => 'Frescura diaria directamente de productores locales de Catalunya.'],
            ['name' => 'Carnicería y Aves', 'icon' => '🥩', 'description' => 'Carnes selectas con corte tradicional artesanal.'],
            ['name' => 'Pescadería', 'icon' => '🐟', 'description' => 'Pescado y marisco fresco de lonja.'],
            ['name' => 'Lácteos y Huevos', 'icon' => '🧀', 'description' => 'Leche de granja, quesos artesanos y huevos camperos.'],
            ['name' => 'Panadería y Dulces', 'icon' => '🥖', 'description' => 'Pan artesano de masa madre y bollería recién horneada.'],
            ['name' => 'Despensa y Conservas', 'icon' => '🥫', 'description' => 'Aceite de oliva virgen extra, legumbres y conservas selectas.'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'description' => $cat['description'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Products
        $productsData = [
            [
                'category' => 'Frutas y Verduras',
                'name' => 'Manzana Golden de Lleida',
                'price' => 2.45,
                'sale_unit' => Product::UNIT_KG,
                'shelf_location' => 'Zona Frutas - Pasillo 1',
                'tracks_expiry' => false,
                'description' => 'Manzanas dulces y crujientes cosechadas en campos de Lleida.',
            ],
            [
                'category' => 'Frutas y Verduras',
                'name' => 'Plátano de Canarias Extra',
                'price' => 2.80,
                'sale_unit' => Product::UNIT_KG,
                'shelf_location' => 'Zona Frutas - Pasillo 1',
                'tracks_expiry' => false,
                'description' => 'Plátanos con motitas de maduración óptima.',
            ],
            [
                'category' => 'Frutas y Verduras',
                'name' => 'Tomate Monterosa Ecológico',
                'price' => 3.90,
                'sale_unit' => Product::UNIT_KG,
                'shelf_location' => 'Zona Verduras - Pasillo 2',
                'tracks_expiry' => false,
                'description' => 'Tomates aromáticos y carnosos, ideales para ensaladas.',
            ],
            [
                'category' => 'Carnicería y Aves',
                'name' => 'Pechuga de Pollo de Corral',
                'price' => 8.95,
                'sale_unit' => Product::UNIT_KG,
                'shelf_location' => 'Mural Refrigerado Carnes',
                'tracks_expiry' => true,
                'description' => 'Pechugas de pollo criado en libertad con alimentación natural.',
            ],
            [
                'category' => 'Carnicería y Aves',
                'name' => 'Hamburguesa de Ternera Gallega 2x150g',
                'price' => 4.50,
                'sale_unit' => Product::UNIT_PACK,
                'shelf_location' => 'Mural Refrigerado Carnes',
                'tracks_expiry' => true,
                'description' => 'Hamburguesas 100% vacuno mayor con sal marina sin aditivos.',
            ],
            [
                'category' => 'Lácteos y Huevos',
                'name' => 'Leche Entera de Granja 1L',
                'price' => 1.35,
                'sale_unit' => Product::UNIT_PIECE,
                'shelf_location' => 'Mural Lácteos A1',
                'tracks_expiry' => true,
                'description' => 'Leche pasteurizada fresca de granjas familiares catalanas.',
            ],
            [
                'category' => 'Lácteos y Huevos',
                'name' => 'Huevos Camperos Docena (L)',
                'price' => 3.20,
                'sale_unit' => Product::UNIT_PACK,
                'shelf_location' => 'Estante Lácteos B2',
                'tracks_expiry' => true,
                'description' => 'Huevos frescos de gallinas camperas criadas al aire libre.',
            ],
            [
                'category' => 'Panadería y Dulces',
                'name' => 'Pan Rústico de Masa Madre 500g',
                'price' => 2.10,
                'sale_unit' => Product::UNIT_PIECE,
                'shelf_location' => 'Mesa Panadería',
                'tracks_expiry' => false,
                'description' => 'Horneado a diario con harina molida a la piedra y fermentación lenta.',
            ],
            [
                'category' => 'Despensa y Conservas',
                'name' => 'Aceite de Oliva Virgen Extra Siurana 500ml',
                'price' => 7.80,
                'sale_unit' => Product::UNIT_PIECE,
                'shelf_location' => 'Pasillo 4 - Aceites',
                'tracks_expiry' => false,
                'description' => 'Aceite arbequina de primera prensada en frío con D.O. Siurana.',
            ],
        ];

        foreach ($productsData as $item) {
            $cat = $categories[$item['category']];
            Product::firstOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'category_id' => $cat->id,
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'sale_unit' => $item['sale_unit'],
                    'shelf_location' => $item['shelf_location'],
                    'tracks_expiry' => $item['tracks_expiry'],
                    'description' => $item['description'],
                    'low_stock_threshold' => 5.000,
                    'is_active' => true,
                ]
            );
        }
    }
}
