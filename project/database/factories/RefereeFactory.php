<?php

namespace Database\Factories;

use App\Models\Referee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referee>
 */
class RefereeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => null,
            'birth_date' => fake()->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'license_number' => fake()->unique()->bothify('LIC-####-??'),
            'years_experience' => fake()->numberBetween(1, 30),
            'category' => 'regional',
            'is_active' => true,
        ];
    }
}
