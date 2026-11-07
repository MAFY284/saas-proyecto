<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FootballDemoSeeder extends Seeder
{
    public function run(): void
    {
        $countryId = $this->id('countries', ['iso2_code' => 'MX'], [
            'name' => 'México',
            'iso3_code' => 'MEX',
        ]);

        $currencyId = $this->id('currencies', ['code' => 'MXN'], [
            'name' => 'Peso mexicano',
            'symbol' => '$',
        ]);

        $languageId = $this->id('languages', ['iso_code' => 'es'], [
            'name' => 'Español',
            'native_name' => 'Español',
        ]);

        $adminDivisionId = $this->id('admin_divisions', [
            'country_id' => $countryId,
            'name' => 'Jalisco',
        ], [
            'code' => 'JAL',
        ]);

        $municipalityId = $this->id('municipalities', [
            'admin_division_id' => $adminDivisionId,
            'name' => 'Guadalajara',
        ], [
            'code' => 'GDL',
        ]);

        $localityId = $this->id('localities', [
            'municipality_id' => $municipalityId,
            'name' => 'Guadalajara Centro',
        ], [
            'postal_code' => '44100',
        ]);

        $addressId = $this->id('addresses', ['name' => 'Estadio Demo'], [
            'country_id' => $countryId,
            'admin_division_id' => $adminDivisionId,
            'municipality_id' => $municipalityId,
            'locality_id' => $localityId,
            'line_1' => 'Av. del Deporte 123',
            'postal_code' => '44100',
        ]);

        $leagueId = $this->id('leagues', ['slug' => 'liga-demo'], [
            'name' => 'Liga Demo',
            'short_name' => 'LD',
            'description' => 'Liga de prueba para los CRUDs.',
            'country_id' => $countryId,
            'currency_id' => $currencyId,
            'time_zone' => 'America/Mexico_City',
            'is_active' => true,
        ]);

        $fieldId = $this->id('fields', ['name' => 'Estadio Azteca'], [
            'address_id' => $addressId,
            'address' => 'Calzada de Tlalpan 3465',
            'surface_type' => 'grass',
            'capacity' => 87000,
            'operating_hours' => '08:00 - 22:00',
            'status' => 'available',
        ]);

        foreach ([
            'Estadio Akron' => 49850,
            'Estadio BBVA' => 53500,
            'Estadio Universitario' => 42000,
            'Estadio Hidalgo' => 30000,
            'Estadio Corregidora' => 34000,
        ] as $fieldName => $capacity) {
            $this->id('fields', ['name' => $fieldName], [
                'address_id' => $addressId,
                'address' => null,
                'surface_type' => 'grass',
                'capacity' => $capacity,
                'operating_hours' => '08:00 - 22:00',
                'status' => 'available',
            ]);
        }

        $homeTeamId = $this->id('teams', ['slug' => 'seleccion-mexicana'], [
            'locality_id' => $localityId,
            'name' => 'Selección Mexicana',
            'short_name' => 'MEX',
            'contact_email' => 'seleccion@example.com',
            'is_active' => true,
        ]);

        $awayTeamId = $this->id('teams', ['slug' => 'mexico-sub-23'], [
            'locality_id' => $localityId,
            'name' => 'México Sub-23',
            'short_name' => 'M23',
            'contact_email' => 'sub23@example.com',
            'is_active' => true,
        ]);

        foreach ([
            'mexico-sub-20' => ['México Sub-20', 'M20'],
            'mexico-sub-17' => ['México Sub-17', 'M17'],
            'mexico-femenil' => ['México Femenil', 'MEXF'],
            'club-america' => ['Club América', 'AME'],
            'chivas' => ['Chivas Guadalajara', 'CHI'],
            'cruz-azul' => ['Cruz Azul', 'CAZ'],
            'pumas' => ['Pumas UNAM', 'PUM'],
            'tigres' => ['Tigres UANL', 'TIG'],
            'rayados' => ['Rayados de Monterrey', 'RAY'],
            'toluca' => ['Deportivo Toluca', 'TOL'],
            'leon' => ['Club León', 'LEO'],
        ] as $slug => [$teamName, $shortName]) {
            $extraTeamId = $this->id('teams', ['slug' => $slug], [
                'locality_id' => $localityId,
                'name' => $teamName,
                'short_name' => $shortName,
                'is_active' => true,
            ]);

            $this->playersForTeam($extraTeamId, [
                1 => ['Jugador', $shortName.' 1', 'GK'],
                4 => ['Jugador', $shortName.' 2', 'DF'],
                8 => ['Jugador', $shortName.' 3', 'MF'],
                9 => ['Jugador', $shortName.' 4', 'FW'],
            ]);
        }

        $homePlayers = $this->playersForTeam($homeTeamId, [
            9 => ['Luis', 'Local', 'FW'],
            10 => ['Mario', 'Delantero', 'MF'],
            4 => ['Andrés', 'Defensa', 'DF'],
            1 => ['Carlos', 'Portero', 'GK'],
        ]);

        $awayPlayers = $this->playersForTeam($awayTeamId, [
            7 => ['Diego', 'Visitante', 'FW'],
            11 => ['Javier', 'Extremo', 'MF'],
            5 => ['Edwin', 'Central', 'DF'],
            12 => ['Pablo', 'Portero', 'GK'],
        ]);

        $referee1 = $this->referee('Ana', 'Pérez');
        $referee2 = $this->referee('Luis', 'Gómez');
        $referee3 = $this->referee('Marta', 'Sánchez');
        $referee4 = $this->referee('Raúl', 'Torres');

        $tournamentId = $this->id('tournaments', [
            'league_id' => $leagueId,
            'name' => 'Torneo Apertura',
            'season' => '2026',
        ], [
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'format' => 'league',
            'status' => 'active',
        ]);

        $finishedMatchId = $this->id('matches', [
            'tournament_id' => $tournamentId,
            'local_team_id' => $homeTeamId,
            'visitor_team_id' => $awayTeamId,
            'date_time' => now()->subDays(7)->setTime(18, 0)->toDateTimeString(),
        ], [
            'field_id' => $fieldId,
            'local_goals' => 2,
            'visitor_goals' => 1,
            'matchday' => 1,
            'status' => 'finished',
            'notes' => 'Partido de demostración con acta.',
        ]);

        $this->id('matches', [
            'tournament_id' => $tournamentId,
            'local_team_id' => $awayTeamId,
            'visitor_team_id' => $homeTeamId,
            'date_time' => now()->subDays(3)->setTime(20, 0)->toDateTimeString(),
        ], [
            'field_id' => $fieldId,
            'local_goals' => 0,
            'visitor_goals' => 0,
            'matchday' => 2,
            'status' => 'finished',
            'notes' => 'Pendiente de acta para probar el CRUD.',
        ]);

        $this->id('matches', [
            'tournament_id' => $tournamentId,
            'local_team_id' => $homeTeamId,
            'visitor_team_id' => $awayTeamId,
            'date_time' => now()->addDays(3)->setTime(19, 30)->toDateTimeString(),
        ], [
            'field_id' => $fieldId,
            'local_goals' => 0,
            'visitor_goals' => 0,
            'matchday' => 3,
            'status' => 'scheduled',
            'notes' => 'Partido programado.',
        ]);

        $sheetId = $this->id('match_sheets', ['match_id' => $finishedMatchId], [
            'match_date' => now()->subDays(7)->setTime(18, 0)->toDateTimeString(),
            'local_score' => 2,
            'visitor_score' => 1,
            'incidents' => 'Sin incidencias relevantes.',
            'status' => 'completed',
            'referee1' => $referee1,
            'referee2' => $referee2,
            'referee3' => $referee3,
            'referee4' => $referee4,
        ]);

        foreach (array_slice($homePlayers, 0, 3) as $player) {
            $this->id('match_sheet_players', [
                'match_sheet_id' => $sheetId,
                'player_id' => $player['id'],
            ], [
                'is_starter' => true,
                'goals' => $player['jersey'] === 9 ? 2 : 0,
                'yellow_cards' => 0,
                'red_cards' => 0,
            ]);
        }

        foreach (array_slice($awayPlayers, 0, 3) as $player) {
            $this->id('match_sheet_players', [
                'match_sheet_id' => $sheetId,
                'player_id' => $player['id'],
            ], [
                'is_starter' => true,
                'goals' => $player['jersey'] === 7 ? 1 : 0,
                'yellow_cards' => $player['jersey'] === 11 ? 1 : 0,
                'red_cards' => 0,
            ]);
        }

    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: string}>  $players
     * @return array<int, array{id: int, jersey: int}>
     */
    private function playersForTeam(int $teamId, array $players): array
    {
        $created = [];

        foreach ($players as $jersey => [$firstName, $lastName, $position]) {
            $personId = $this->id('person_data', [
                'first_name' => $firstName,
                'last_name' => $lastName,
            ], [
                'birth_date' => now()->subYears(24)->toDateString(),
                'status' => 'active',
            ]);

            $playerId = $this->id('players', [
                'person_id' => $personId,
                'type_id' => 'player',
            ], [
                'goals' => 0,
            ]);

            $this->id('team_players', [
                'team_id' => $teamId,
                'player_id' => $playerId,
            ], [
                'captain' => $jersey === 9,
                'jersey_number' => $jersey,
            ]);

            $created[] = ['id' => $playerId, 'jersey' => $jersey];
        }

        return $created;
    }

    private function referee(string $firstName, string $lastName): int
    {
        $personId = $this->id('person_data', [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ], [
            'birth_date' => now()->subYears(35)->toDateString(),
            'status' => 'active',
        ]);

        return $this->id('players', [
            'person_id' => $personId,
            'type_id' => 'referee',
        ], [
            'goals' => 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $where
     * @param  array<string, mixed>  $values
     */
    private function id(string $table, array $where, array $values): int
    {
        $row = DB::table($table)->where($where)->first();

        if ($row) {
            return (int) $row->id;
        }

        $data = array_merge($where, $values);

        if (Schema::hasColumn($table, 'created_at') && ! array_key_exists('created_at', $data)) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn($table, 'updated_at') && ! array_key_exists('updated_at', $data)) {
            $data['updated_at'] = now();
        }

        return (int) DB::table($table)->insertGetId($data);
    }
}
