<?php

namespace Tests\Feature;

use App\Models\Field;
use App\Models\Language;
use App\Models\PersonData;
use App\Models\Player;
use App\Models\RefereeCard;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RefereeCardManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_no_entra_al_crud_de_cedulas_arbitrales(): void
    {
        $this->get(route('referee-cards.index'))->assertRedirect(route('login'));
        $this->get(route('referee-cards.create'))->assertRedirect(route('login'));
    }

    public function test_se_puede_registrar_una_cedula_con_jugadores_goles_y_tarjetas(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['user'])
            ->post(route('referee-cards.store'), [
                'match_id' => $fixture['match_id'],
                'field_id' => $fixture['field_id'],
                'city' => 'Guadalajara',
                'match_date' => '2026-11-05',
                'observations' => 'Partido sin incidencias.',
                'players' => [
                    $fixture['local_player'] => ['selected' => '1', 'goals' => '2', 'yellow_cards' => '1', 'direct_red_cards' => '0'],
                    $fixture['visitor_player'] => ['selected' => '1', 'goals' => '0', 'yellow_cards' => '0', 'direct_red_cards' => '1'],
                ],
            ])
            ->assertRedirect(route('referee-cards.index'))
            ->assertSessionHas('success');

        $card = RefereeCard::query()->firstOrFail();

        $this->assertSame($fixture['match_id'], $card->match_id);
        $this->assertSame($fixture['field_id'], $card->field_id);
        $this->assertSame('Guadalajara', $card->city);
        $this->assertSame('Partido sin incidencias.', $card->observations);
        $this->assertSame('2026-11-05', $card->match_date->format('Y-m-d'));

        $this->assertDatabaseHas('referee_card_players', [
            'referee_card_id' => $card->id,
            'player_id' => $fixture['local_player'],
            'goals' => 2,
            'yellow_cards' => 1,
            'direct_red_cards' => 0,
            'indirect_red_cards' => 0,
        ]);

        // Roja directa: no tiene relación con las amarillas.
        $this->assertDatabaseHas('referee_card_players', [
            'referee_card_id' => $card->id,
            'player_id' => $fixture['visitor_player'],
            'yellow_cards' => 0,
            'direct_red_cards' => 1,
            'indirect_red_cards' => 0,
        ]);
    }

    public function test_dos_amarillas_generan_una_roja_indirecta(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['user'])
            ->post(route('referee-cards.store'), [
                'match_id' => $fixture['match_id'],
                'field_id' => $fixture['field_id'],
                'city' => 'Guadalajara',
                'match_date' => '2026-11-05',
                'players' => [
                    $fixture['local_player'] => ['selected' => '1', 'goals' => '0', 'yellow_cards' => '2', 'direct_red_cards' => '0'],
                ],
            ])
            ->assertRedirect(route('referee-cards.index'));

        $this->assertDatabaseHas('referee_card_players', [
            'player_id' => $fixture['local_player'],
            'yellow_cards' => 2,
            'direct_red_cards' => 0,
            'indirect_red_cards' => 1,
        ]);
    }

    public function test_una_amarilla_no_genera_roja_indirecta(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['user'])
            ->post(route('referee-cards.store'), [
                'match_id' => $fixture['match_id'],
                'field_id' => $fixture['field_id'],
                'city' => 'Guadalajara',
                'match_date' => '2026-11-05',
                'players' => [
                    $fixture['local_player'] => ['selected' => '1', 'yellow_cards' => '1'],
                ],
            ]);

        $this->assertDatabaseHas('referee_card_players', [
            'player_id' => $fixture['local_player'],
            'yellow_cards' => 1,
            'indirect_red_cards' => 0,
        ]);
    }

    public function test_el_listado_muestra_la_cedula_con_sus_totales(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['user'])->post(route('referee-cards.store'), [
            'match_id' => $fixture['match_id'],
            'field_id' => $fixture['field_id'],
            'city' => 'Zapopan',
            'match_date' => '2026-11-05',
            'players' => [
                $fixture['local_player'] => ['selected' => '1', 'goals' => '3', 'yellow_cards' => '2', 'direct_red_cards' => '0'],
                $fixture['visitor_player'] => ['selected' => '1', 'goals' => '1', 'yellow_cards' => '1', 'direct_red_cards' => '1'],
            ],
        ]);

        $this->actingAs($fixture['user'])
            ->get(route('referee-cards.index'))
            ->assertOk()
            ->assertSee('Zapopan')
            ->assertSee('R. directa')
            ->assertSee('R. indirecta')
            ->assertSee('Cédula arbitral registrada correctamente.', false);
    }

    public function test_rechaza_un_partido_que_ya_tiene_cedula(): void
    {
        $fixture = $this->fixture();

        $payload = [
            'match_id' => $fixture['match_id'],
            'field_id' => $fixture['field_id'],
            'city' => 'Guadalajara',
            'match_date' => '2026-11-05',
        ];

        $this->actingAs($fixture['user'])->post(route('referee-cards.store'), $payload);

        $this->actingAs($fixture['user'])
            ->post(route('referee-cards.store'), $payload)
            ->assertSessionHasErrors(['match_id' => 'Ese partido ya tiene una cédula arbitral registrada.']);

        $this->assertSame(1, RefereeCard::query()->count());
    }

    public function test_rechaza_jugadores_que_no_son_del_partido(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['user'])
            ->post(route('referee-cards.store'), [
                'match_id' => $fixture['match_id'],
                'field_id' => $fixture['field_id'],
                'city' => 'Guadalajara',
                'match_date' => '2026-11-05',
                'players' => [
                    $fixture['outsider_player'] => ['selected' => '1', 'goals' => '1'],
                ],
            ])
            ->assertSessionHasErrors(['players']);

        $this->assertSame(0, RefereeCard::query()->count());
    }

    public function test_rechaza_mas_de_dos_amarillas_y_mas_de_una_roja_directa(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['user'])
            ->post(route('referee-cards.store'), [
                'match_id' => $fixture['match_id'],
                'field_id' => $fixture['field_id'],
                'city' => 'Guadalajara',
                'match_date' => '2026-11-05',
                'players' => [
                    $fixture['local_player'] => ['selected' => '1', 'yellow_cards' => '3', 'direct_red_cards' => '2'],
                ],
            ])
            ->assertSessionHasErrors([
                'players.'.$fixture['local_player'].'.yellow_cards',
                'players.'.$fixture['local_player'].'.direct_red_cards',
            ]);

        $this->assertSame(0, RefereeCard::query()->count());
    }

    public function test_se_puede_actualizar_y_eliminar_una_cedula(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['user'])->post(route('referee-cards.store'), [
            'match_id' => $fixture['match_id'],
            'field_id' => $fixture['field_id'],
            'city' => 'Guadalajara',
            'match_date' => '2026-11-05',
            'players' => [
                $fixture['local_player'] => ['selected' => '1', 'goals' => '1'],
            ],
        ]);

        $card = RefereeCard::query()->firstOrFail();

        $this->actingAs($fixture['user'])
            ->put(route('referee-cards.update', $card), [
                'match_id' => $fixture['match_id'],
                'field_id' => $fixture['field_id'],
                'city' => 'Tlaquepaque',
                'match_date' => '2026-11-06',
                'observations' => 'Actualizada.',
                'players' => [
                    $fixture['visitor_player'] => ['selected' => '1', 'goals' => '2', 'yellow_cards' => '2', 'direct_red_cards' => '0'],
                ],
            ])
            ->assertRedirect(route('referee-cards.index'));

        $card->refresh();

        $this->assertSame('Tlaquepaque', $card->city);
        $this->assertSame('Actualizada.', $card->observations);
        $this->assertSame(1, $card->entries()->count());
        $this->assertDatabaseHas('referee_card_players', [
            'referee_card_id' => $card->id,
            'player_id' => $fixture['visitor_player'],
            'goals' => 2,
            'yellow_cards' => 2,
            'direct_red_cards' => 0,
            'indirect_red_cards' => 1,
        ]);

        $this->actingAs($fixture['user'])
            ->delete(route('referee-cards.destroy'), ['ids' => [$card->id]])
            ->assertRedirect(route('referee-cards.index'));

        $this->assertSame(0, RefereeCard::query()->count());
        $this->assertSame(0, DB::table('referee_card_players')->count());
    }

    /**
     * Crea el mínimo esquema de datos que necesita una cédula arbitral.
     *
     * @return array<string, int|User>
     */
    private function fixture(): array
    {
        $language = Language::firstOrCreate(
            ['iso_code' => 'es'],
            ['name' => 'Español', 'native_name' => 'Español']
        );

        $user = User::factory()->create(['language_id' => $language->id]);

        $countryId = DB::table('countries')->insertGetId([
            'name' => 'México', 'iso2_code' => 'MX', 'created_at' => now(),
        ]);

        $currencyId = DB::table('currencies')->insertGetId([
            'name' => 'Peso', 'code' => 'MXN', 'symbol' => '$', 'created_at' => now(),
        ]);

        $divisionId = DB::table('admin_divisions')->insertGetId([
            'country_id' => $countryId, 'name' => 'Jalisco', 'created_at' => now(),
        ]);

        $municipalityId = DB::table('municipalities')->insertGetId([
            'admin_division_id' => $divisionId, 'name' => 'Guadalajara', 'created_at' => now(),
        ]);

        $localityId = DB::table('localities')->insertGetId([
            'municipality_id' => $municipalityId, 'name' => 'Guadalajara Centro', 'created_at' => now(),
        ]);

        $addressId = DB::table('addresses')->insertGetId([
            'name' => 'Estadio', 'country_id' => $countryId, 'admin_division_id' => $divisionId,
            'municipality_id' => $municipalityId, 'locality_id' => $localityId,
            'line_1' => 'Calle 1', 'created_at' => now(),
        ]);

        $field = Field::create([
            'address_id' => $addressId, 'name' => 'Estadio Azteca', 'status' => 'available',
        ]);

        $leagueId = DB::table('leagues')->insertGetId([
            'name' => 'Liga Demo', 'slug' => 'liga-demo', 'country_id' => $countryId,
            'currency_id' => $currencyId, 'time_zone' => 'UTC', 'is_active' => 1, 'created_at' => now(),
        ]);

        $tournamentId = DB::table('tournaments')->insertGetId([
            'league_id' => $leagueId, 'name' => 'Torneo Demo', 'status' => 'active', 'created_at' => now(),
        ]);

        $localTeam = Team::create([
            'locality_id' => $localityId, 'name' => 'Equipo Local', 'slug' => 'equipo-local', 'is_active' => true,
        ]);

        $visitorTeam = Team::create([
            'locality_id' => $localityId, 'name' => 'Equipo Visitante', 'slug' => 'equipo-visitante', 'is_active' => true,
        ]);

        $outsideTeam = Team::create([
            'locality_id' => $localityId, 'name' => 'Equipo Ajeno', 'slug' => 'equipo-ajeno', 'is_active' => true,
        ]);

        $localPlayer = $this->playerFor($localTeam, 'Luis', 'Local');
        $visitorPlayer = $this->playerFor($visitorTeam, 'Diego', 'Visitante');
        $outsiderPlayer = $this->playerFor($outsideTeam, 'Ajeno', 'Jugador');

        $matchId = DB::table('matches')->insertGetId([
            'tournament_id' => $tournamentId,
            'local_team_id' => $localTeam->id,
            'visitor_team_id' => $visitorTeam->id,
            'field_id' => $field->id,
            'date_time' => '2026-11-05 18:00:00',
            'local_goals' => 0,
            'visitor_goals' => 0,
            'status' => 'scheduled',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'user' => $user,
            'field_id' => $field->id,
            'match_id' => $matchId,
            'local_player' => $localPlayer->id,
            'visitor_player' => $visitorPlayer->id,
            'outsider_player' => $outsiderPlayer->id,
        ];
    }

    private function playerFor(Team $team, string $firstName, string $lastName): Player
    {
        $person = PersonData::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => 'active',
        ]);

        $player = Player::create([
            'person_id' => $person->id,
            'type_id' => 'player',
        ]);

        $player->teams()->attach($team->id, ['captain' => false, 'jersey_number' => 10]);

        return $player;
    }
}
