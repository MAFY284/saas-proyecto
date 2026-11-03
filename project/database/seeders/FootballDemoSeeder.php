<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\League;
use App\Models\MatchReport;
use App\Models\MatchReportPlayer;
use App\Models\Player;
use App\Models\Referee;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class FootballDemoSeeder extends Seeder
{
    public function run(): void
    {
        $league = League::query()->firstOrCreate(
            ['slug' => 'liga-demo'],
            [
                'name' => 'Liga Demo',
                'short_name' => 'LD',
                'description' => 'Liga de prueba para el CRUD de partidos y actas.',
                'time_zone' => 'UTC',
                'is_active' => true,
            ],
        );

        $tournament = Tournament::query()->firstOrCreate(
            [
                'league_id' => $league->id,
                'name' => 'Torneo Apertura',
                'season' => '2026',
            ],
            [
                'start_date' => now()->startOfYear()->toDateString(),
                'end_date' => now()->endOfYear()->toDateString(),
                'format' => 'league',
                'status' => 'active',
            ],
        );

        $home = Team::query()->firstOrCreate(
            ['league_id' => $league->id, 'slug' => 'team-azul'],
            [
                'name' => 'Team Azul',
                'short_name' => 'AZU',
                'coach_name' => 'Entrenador Azul',
                'is_active' => true,
            ],
        );

        $away = Team::query()->firstOrCreate(
            ['league_id' => $league->id, 'slug' => 'team-rojo'],
            [
                'name' => 'Team Rojo',
                'short_name' => 'ROJ',
                'coach_name' => 'Entrenador Rojo',
                'is_active' => true,
            ],
        );

        $venue = Venue::query()->firstOrCreate(
            ['name' => 'Estadio Demo'],
            [
                'address' => 'Av. del Deporte 123',
                'surface_type' => 'grass',
                'capacity' => 15000,
                'operating_hours' => '08:00 - 22:00',
                'status' => 'available',
            ],
        );

        $referee = Referee::query()->firstOrCreate(
            ['license_number' => 'DEMO-001'],
            [
                'first_name' => 'Ana',
                'last_name' => 'Pérez',
                'phone' => '555-100-2000',
                'email' => 'ana.perez@example.com',
                'years_experience' => 12,
                'category' => 'regional',
                'is_active' => true,
            ],
        );

        $refereeTwo = Referee::query()->firstOrCreate(
            ['license_number' => 'DEMO-002'],
            [
                'first_name' => 'Luis',
                'last_name' => 'Gómez',
                'years_experience' => 8,
                'category' => 'regional',
                'is_active' => true,
            ],
        );

        $homePlayers = $this->playersFor($home, [
            9 => ['Luis', 'Local', 'FW'],
            10 => ['Mario', 'Delantero', 'MF'],
            4 => ['Andrés', 'Defensa', 'DF'],
            1 => ['Carlos', 'Portero', 'GK'],
        ]);

        $awayPlayers = $this->playersFor($away, [
            7 => ['Diego', 'Visitante', 'FW'],
            11 => ['Javier', 'Extremo', 'MF'],
            5 => ['Edwin', 'Central', 'DF'],
            12 => ['Pablo', 'Portero', 'GK'],
        ]);

        $finished = Game::query()->firstOrCreate(
            [
                'tournament_id' => $tournament->id,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'match_date' => now()->subDays(7)->setTime(18, 0),
            ],
            [
                'venue_id' => $venue->id,
                'referee_id' => $referee->id,
                'home_goals' => 2,
                'away_goals' => 1,
                'matchday' => 1,
                'status' => 'finished',
                'notes' => 'Partido de demostración con acta.',
            ],
        );

        Game::query()->firstOrCreate(
            [
                'tournament_id' => $tournament->id,
                'home_team_id' => $away->id,
                'away_team_id' => $home->id,
                'match_date' => now()->subDays(3)->setTime(20, 0),
            ],
            [
                'venue_id' => $venue->id,
                'referee_id' => $refereeTwo->id,
                'home_goals' => 0,
                'away_goals' => 0,
                'matchday' => 2,
                'status' => 'finished',
                'notes' => 'Pendiente de acta para probar el CRUD.',
            ],
        );

        Game::query()->firstOrCreate(
            [
                'tournament_id' => $tournament->id,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'match_date' => now()->addDays(3)->setTime(19, 30),
            ],
            [
                'venue_id' => $venue->id,
                'referee_id' => $referee->id,
                'home_goals' => null,
                'away_goals' => null,
                'matchday' => 3,
                'status' => 'scheduled',
                'notes' => 'Partido programado.',
            ],
        );

        $report = MatchReport::query()->firstOrCreate(
            ['match_id' => $finished->id],
            [
                'referee_id' => $referee->id,
                'venue_id' => $venue->id,
                'match_date' => $finished->match_date,
                'home_score' => 2,
                'away_score' => 1,
                'incidents' => 'Sin incidencias relevantes.',
                'status' => 'completed',
            ],
        );

        foreach (array_slice($homePlayers, 0, 3) as $player) {
            MatchReportPlayer::query()->firstOrCreate(
                ['match_report_id' => $report->id, 'player_id' => $player->id],
                [
                    'team_id' => $home->id,
                    'is_starter' => true,
                    'goals' => $player->jersey_number === 9 ? 2 : 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                ],
            );
        }

        foreach (array_slice($awayPlayers, 0, 3) as $player) {
            MatchReportPlayer::query()->firstOrCreate(
                ['match_report_id' => $report->id, 'player_id' => $player->id],
                [
                    'team_id' => $away->id,
                    'is_starter' => true,
                    'goals' => $player->jersey_number === 7 ? 1 : 0,
                    'yellow_cards' => $player->jersey_number === 11 ? 1 : 0,
                    'red_cards' => 0,
                ],
            );
        }
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: string}>  $players
     * @return array<int, Player>
     */
    private function playersFor(Team $team, array $players): array
    {
        $created = [];

        foreach ($players as $jersey => [$firstName, $lastName, $position]) {
            $created[] = Player::query()->firstOrCreate(
                ['team_id' => $team->id, 'jersey_number' => $jersey],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'position' => $position,
                    'status' => 'active',
                ],
            );
        }

        return $created;
    }
}
