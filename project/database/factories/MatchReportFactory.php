<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\MatchReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatchReport>
 */
class MatchReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'match_id' => Game::factory(),
            'referee_id' => null,
            'venue_id' => null,
            'match_date' => now(),
            'home_score' => 0,
            'away_score' => 0,
            'incidents' => null,
            'status' => 'completed',
        ];
    }
}
