<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        return [
            'tournament_id' => Tournament::factory(),
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'venue_id' => null,
            'referee_id' => null,
            'match_date' => now()->addDays(fake()->numberBetween(1, 30)),
            'home_goals' => null,
            'away_goals' => null,
            'matchday' => fake()->numberBetween(1, 20),
            'status' => 'scheduled',
            'notes' => null,
        ];
    }
}
