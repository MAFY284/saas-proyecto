<?php

namespace Database\Factories;

use App\Models\League;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tournament>
 */
class TournamentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'league_id' => League::factory(),
            'name' => fake()->unique()->words(3, true),
            'season' => (string) now()->year,
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'format' => 'league',
            'status' => 'active',
        ];
    }
}
