<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users for each role
        $admin = User::firstOrCreate(
            ['email' => 'admin@localmarket.test'],
            [
                'name' => 'Administrador LocalMarket',
                'password' => Hash::make('password123'),
                'role' => User::ADMIN,
                'phone' => '931234567',
                'is_active' => true,
            ]
        );

        $warehouseTech = User::firstOrCreate(
            ['email' => 'almacen@localmarket.test'],
            [
                'name' => 'Técnico Almacén',
                'password' => Hash::make('password123'),
                'role' => User::WAREHOUSE,
                'phone' => '931234568',
                'is_active' => true,
            ]
        );

        $cashier = User::firstOrCreate(
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

        // 2. Locations (Warehouse & Store)
        $warehouse = Location::firstOrCreate(
            ['name' => 'Almacén Central Zona Franca'],
            [
                'type' => Location::TYPE_WAREHOUSE,
                'address' => 'Carrer D, Sector B, Zona Franca, 08040 Barcelona',
                'is_online_source' => false,
                'is_active' => true,
            ]
        );

        $store = Location::firstOrCreate(
            ['name' => 'Tienda LocalMarket Barcelona'],
            [
                'type' => Location::TYPE_STORE,
                'address' => 'Carrer del Mercat, 12, Ciutat Vella, 08001 Barcelona',
                'is_online_source' => true, // Online orders are served from here
                'is_active' => true,
            ]
        );

        // 3. Categories
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

        // 4. Products with initial stock
        $productsData = [
            [
                'category' => 'Frutas y Verduras',
                'name' => 'Manzana Golden de Lleida',
                'price' => 2.45,
                'sale_unit' => Product::UNIT_KG,
                'shelf_location' => 'Zona Frutas - Pasillo 1',
                'tracks_expiry' => false,
                'description' => 'Manzanas dulces y crujientes cosechadas en campos de Lleida.',
                'stock_store' => 25.5,
                'stock_warehouse' => 150.0,
            ],
            [
                'category' => 'Frutas y Verduras',
                'name' => 'Plátano de Canarias Extra',
                'price' => 2.80,
                'sale_unit' => Product::UNIT_KG,
                'shelf_location' => 'Zona Frutas - Pasillo 1',
                'tracks_expiry' => false,
                'description' => 'Plátanos con motitas de maduración óptima.',
                'stock_store' => 18.0,
                'stock_warehouse' => 80.0,
            ],
            [
                'category' => 'Frutas y Verduras',
                'name' => 'Tomate Monterosa Ecológico',
                'price' => 3.90,
                'sale_unit' => Product::UNIT_KG,
                'shelf_location' => 'Zona Verduras - Pasillo 2',
                'tracks_expiry' => false,
                'description' => 'Tomates aromáticos y carnosos, ideales para ensaladas.',
                'stock_store' => 4.0, // Low stock trigger
                'stock_warehouse' => 35.0,
            ],
            [
                'category' => 'Carnicería y Aves',
                'name' => 'Pechuga de Pollo de Corral',
                'price' => 8.95,
                'sale_unit' => Product::UNIT_KG,
                'shelf_location' => 'Mural Refrigerado Carnes',
                'tracks_expiry' => true,
                'description' => 'Pechugas de pollo criado en libertad con alimentación natural.',
                'stock_store' => 12.0,
                'stock_warehouse' => 40.0,
                'lot' => 'LOT-POL-901',
                'expiry' => now()->addDays(2)->format('Y-m-d'), // Near expiry alert
            ],
            [
                'category' => 'Carnicería y Aves',
                'name' => 'Hamburguesa de Ternera Gallega 2x150g',
                'price' => 4.50,
                'sale_unit' => Product::UNIT_PACK,
                'shelf_location' => 'Mural Refrigerado Carnes',
                'tracks_expiry' => true,
                'description' => 'Hamburguesas 100% vacuno mayor con sal marina sin aditivos.',
                'stock_store' => 15.0,
                'stock_warehouse' => 50.0,
                'lot' => 'LOT-HAM-442',
                'expiry' => now()->addDays(5)->format('Y-m-d'),
            ],
            [
                'category' => 'Lácteos y Huevos',
                'name' => 'Leche Entera de Granja 1L',
                'price' => 1.35,
                'sale_unit' => Product::UNIT_PIECE,
                'shelf_location' => 'Mural Lácteos A1',
                'tracks_expiry' => true,
                'description' => 'Leche pasteurizada fresca de granjas familiares catalanas.',
                'stock_store' => 30.0,
                'stock_warehouse' => 120.0,
                'lot' => 'LOT-LEC-811',
                'expiry' => now()->addDays(6)->format('Y-m-d'),
            ],
            [
                'category' => 'Lácteos y Huevos',
                'name' => 'Huevos Camperos Docena (L)',
                'price' => 3.20,
                'sale_unit' => Product::UNIT_PACK,
                'shelf_location' => 'Estante Lácteos B2',
                'tracks_expiry' => true,
                'description' => 'Huevos frescos de gallinas camperas criadas al aire libre.',
                'stock_store' => 20.0,
                'stock_warehouse' => 60.0,
                'lot' => 'LOT-HUE-203',
                'expiry' => now()->addDays(14)->format('Y-m-d'),
            ],
            [
                'category' => 'Panadería y Dulces',
                'name' => 'Pan Rústico de Masa Madre 500g',
                'price' => 2.10,
                'sale_unit' => Product::UNIT_PIECE,
                'shelf_location' => 'Mesa Panadería',
                'tracks_expiry' => false,
                'description' => 'Horneado a diario con harina molida a la piedra y fermentación lenta.',
                'stock_store' => 22.0,
                'stock_warehouse' => 0.0,
            ],
            [
                'category' => 'Despensa y Conservas',
                'name' => 'Aceite de Oliva Virgen Extra Siurana 500ml',
                'price' => 7.80,
                'sale_unit' => Product::UNIT_PIECE,
                'shelf_location' => 'Pasillo 4 - Aceites',
                'tracks_expiry' => false,
                'description' => 'Aceite arbequina de primera prensada en frío con D.O. Siurana.',
                'stock_store' => 14.0,
                'stock_warehouse' => 48.0,
            ],
        ];

        foreach ($productsData as $item) {
            $cat = $categories[$item['category']];
            $product = Product::firstOrCreate(
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

            // Seed store stock
            if ($item['stock_store'] > 0) {
                StockLevel::updateOrCreate(
                    ['product_id' => $product->id, 'location_id' => $store->id],
                    ['quantity' => $item['stock_store'], 'reserved' => 0]
                );

                StockMovement::firstOrCreate(
                    ['product_id' => $product->id, 'location_id' => $store->id, 'type' => StockMovement::TYPE_RECEIPT],
                    [
                        'quantity' => $item['stock_store'],
                        'reason' => 'Inventario inicial tienda',
                        'user_id' => $warehouseTech->id,
                    ]
                );
            }

            // Seed warehouse stock
            if ($item['stock_warehouse'] > 0) {
                StockLevel::updateOrCreate(
                    ['product_id' => $product->id, 'location_id' => $warehouse->id],
                    ['quantity' => $item['stock_warehouse'], 'reserved' => 0]
                );

                StockMovement::firstOrCreate(
                    ['product_id' => $product->id, 'location_id' => $warehouse->id, 'type' => StockMovement::TYPE_RECEIPT],
                    [
                        'quantity' => $item['stock_warehouse'],
                        'reason' => 'Inventario inicial almacén',
                        'user_id' => $warehouseTech->id,
                    ]
                );
            }

            // Seed batch if perishable
            if ($product->tracks_expiry && isset($item['lot'], $item['expiry'])) {
                Batch::firstOrCreate(
                    ['product_id' => $product->id, 'lot_code' => $item['lot']],
                    [
                        'location_id' => $store->id,
                        'expiry_date' => $item['expiry'],
                        'quantity' => $item['stock_store'],
                    ]
                );
            }
        }
    }
}
