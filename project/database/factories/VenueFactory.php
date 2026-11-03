<?php

namespace Database\Factories;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Stadium',
            'address' => fake()->address(),
            'surface_type' => 'grass',
            'capacity' => fake()->numberBetween(1000, 50000),
            'operating_hours' => '08:00 - 22:00',
            'status' => 'available',
        ];
    }
}
