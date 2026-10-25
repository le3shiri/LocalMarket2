<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Tienda '.fake()->city(),
            'type' => Location::TYPE_STORE,
            'address' => fake()->streetAddress().', Barcelona',
            'is_online_source' => false,
            'is_active' => true,
        ];
    }

    public function warehouse(): static
    {
        return $this->state(fn () => [
            'name' => 'Almacén Central',
            'type' => Location::TYPE_WAREHOUSE,
            'is_online_source' => false,
        ]);
    }

    public function onlineSource(): static
    {
        return $this->state(fn () => [
            'name' => 'Tienda Central Barcelona',
            'type' => Location::TYPE_STORE,
            'is_online_source' => true,
        ]);
    }
}
