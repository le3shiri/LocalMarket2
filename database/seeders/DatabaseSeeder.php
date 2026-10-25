<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
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

        // 2. Warehouse Technician
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

        // 3. Store Cashier
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

        // 4. Active Customer
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

        // 5. Deactivated Customer (for testing inactive login blocks)
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
    }
}
