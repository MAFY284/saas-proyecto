<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'jersey_number' => fake()->numberBetween(1, 99),
            'position' => fake()->randomElement(['GK', 'DF', 'MF', 'FW']),
            'birth_date' => fake()->dateTimeBetween('-40 years', '-16 years')->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}
