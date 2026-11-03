<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\League;
use App\Models\MatchReport;
use App\Models\MatchReportPlayer;
use App\Models\Player;
use App\Models\Referee;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchReportManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_no_entra_al_crud_de_actas(): void
    {
        $this->get(route('match-reports.index'))->assertRedirect(route('login'));
        $this->get(route('match-reports.create'))->assertRedirect(route('login'));
    }

    public function test_se_puede_crear_un_acta_con_jugadores(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();

        $this->actingAs($actor)
            ->post(route('match-reports.store'), $this->payload($context))
            ->assertRedirect(route('match-reports.index'))
            ->assertSessionHas('status', 'Acta creada.');

        $report = MatchReport::query()->first();

        $this->assertNotNull($report);
        $this->assertSame($context['game']->id, $report->match_id);
        $this->assertSame(2, $report->home_score);
        $this->assertSame(1, $report->away_score);
        $this->assertSame('completed', $report->status);
        $this->assertSame(2, $report->entries()->count());
        $this->assertDatabaseHas('match_report_players', [
            'match_report_id' => $report->id,
            'player_id' => $context['homePlayer1']->id,
            'team_id' => $context['home']->id,
            'goals' => 1,
        ]);
    }

    public function test_no_se_puede_crear_dos_actas_para_el_mismo_partido(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();
        MatchReport::factory()->create(['match_id' => $context['game']->id]);

        $this->actingAs($actor)
            ->from(route('match-reports.create'))
            ->post(route('match-reports.store'), $this->payload($context))
            ->assertRedirect(route('match-reports.create'))
            ->assertSessionHasErrors(['match_id' => 'Ese partido ya tiene un acta registrada.']);

        $this->assertSame(1, MatchReport::query()->count());
    }

    public function test_crear_acta_rechaza_cada_restriccion_de_campo(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();

        $cases = [
            ['match_id', ['match_id' => 99999], 'El partido no es válido.'],
            ['referee_id', ['referee_id' => 99999], 'El árbitro no es válido.'],
            ['venue_id', ['venue_id' => 99999], 'La sede no es válida.'],
            ['match_date', ['match_date' => ''], 'La fecha del acta es obligatoria.'],
            ['match_date', ['match_date' => 'no-es-fecha'], 'La fecha del acta no es válida.'],
            ['home_score', ['home_score' => -1], 'Los goles del local no pueden ser negativos.'],
            ['away_score', ['away_score' => -1], 'Los goles del visitante no pueden ser negativos.'],
            ['status', ['status' => 'no-existe'], 'El estado no es válido.'],
            ['incidents', ['incidents' => str_repeat('a', 5001)], 'Las incidencias no pueden superar 5000 caracteres.'],
        ];

        foreach ($cases as [$field, $override, $message]) {
            $before = MatchReport::query()->count();

            $response = $this->actingAs($actor)
                ->from(route('match-reports.create'))
                ->post(route('match-reports.store'), $this->payload($context, $override));

            $response->assertRedirect(route('match-reports.create'));
            $response->assertSessionHasErrors([$field => $message]);

            $this->assertSame(
                $before,
                MatchReport::query()->count(),
                "Se creó un acta con {$field} inválido."
            );
        }
    }

    public function test_rechaza_jugadores_que_no_pertenecen_al_partido(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();
        $otherTeam = Team::factory()->create([
            'league_id' => $context['league']->id,
            'name' => 'Equipo Ajeno',
            'slug' => 'equipo-ajeno',
        ]);
        $otherPlayer = Player::factory()->create([
            'team_id' => $otherTeam->id,
            'jersey_number' => 99,
        ]);

        $this->actingAs($actor)
            ->from(route('match-reports.create'))
            ->post(route('match-reports.store'), $this->payload($context, [
                'players' => [
                    $otherPlayer->id => [
                        'selected' => '1',
                        'is_starter' => '1',
                        'goals' => 0,
                        'yellow_cards' => 0,
                        'red_cards' => 0,
                    ],
                ],
            ]))
            ->assertRedirect(route('match-reports.create'))
            ->assertSessionHasErrors(['players' => 'Uno de los jugadores no pertenece a los equipos del partido.']);

        $this->assertSame(0, MatchReport::query()->count());
    }

    public function test_se_puede_editar_un_acta_y_sincronizar_jugadores(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();
        $report = MatchReport::factory()->create(['match_id' => $context['game']->id]);
        MatchReportPlayer::query()->create([
            'match_report_id' => $report->id,
            'player_id' => $context['homePlayer2']->id,
            'team_id' => $context['home']->id,
            'is_starter' => true,
            'goals' => 0,
            'yellow_cards' => 0,
            'red_cards' => 0,
        ]);

        $this->actingAs($actor)
            ->put(route('match-reports.update', $report), $this->payload($context, [
                'home_score' => 4,
                'away_score' => 2,
                'status' => 'draft',
            ]))
            ->assertRedirect(route('match-reports.index'))
            ->assertSessionHas('status', 'Acta actualizada.');

        $report->refresh();

        $this->assertSame(4, $report->home_score);
        $this->assertSame(2, $report->away_score);
        $this->assertSame('draft', $report->status);
        $this->assertSame(2, $report->entries()->count());
        $this->assertDatabaseMissing('match_report_players', [
            'match_report_id' => $report->id,
            'player_id' => $context['homePlayer2']->id,
        ]);
    }

    public function test_se_puede_eliminar_un_acta_y_sus_jugadores(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();
        $report = MatchReport::factory()->create(['match_id' => $context['game']->id]);
        MatchReportPlayer::query()->create([
            'match_report_id' => $report->id,
            'player_id' => $context['homePlayer1']->id,
            'team_id' => $context['home']->id,
            'is_starter' => true,
            'goals' => 0,
            'yellow_cards' => 0,
            'red_cards' => 0,
        ]);

        $this->actingAs($actor)
            ->delete(route('match-reports.destroy'), ['ids' => [$report->id]])
            ->assertRedirect(route('match-reports.index'))
            ->assertSessionHas('status', 'Acta eliminada.');

        $this->assertModelMissing($report);
        $this->assertSame(0, MatchReportPlayer::query()->count());
    }

    public function test_el_index_de_actas_carga_el_partido(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();
        MatchReport::factory()->create([
            'match_id' => $context['game']->id,
            'status' => 'completed',
        ]);

        $this->actingAs($actor)
            ->get(route('match-reports.index'))
            ->assertOk()
            ->assertSee($context['home']->name)
            ->assertSee($context['away']->name);
    }

    /**
     * @return array<string, mixed>
     */
    private function context(): array
    {
        $league = League::factory()->create(['name' => 'Liga Test', 'slug' => 'liga-test']);
        $tournament = Tournament::factory()->create([
            'league_id' => $league->id,
            'name' => 'Torneo Test',
        ]);
        $home = Team::factory()->create([
            'league_id' => $league->id,
            'name' => 'Local FC',
            'slug' => 'local-fc',
        ]);
        $away = Team::factory()->create([
            'league_id' => $league->id,
            'name' => 'Visitante FC',
            'slug' => 'visitante-fc',
        ]);
        $venue = Venue::factory()->create(['name' => 'Estadio Test']);
        $referee = Referee::factory()->create(['first_name' => 'Ana', 'last_name' => 'Pérez']);
        $game = Game::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'venue_id' => $venue->id,
            'referee_id' => $referee->id,
            'match_date' => '2026-12-01 18:00:00',
            'home_goals' => 2,
            'away_goals' => 1,
            'status' => 'finished',
        ]);

        $homePlayer1 = Player::factory()->create([
            'team_id' => $home->id,
            'jersey_number' => 9,
            'first_name' => 'Luis',
            'last_name' => 'Local',
        ]);
        $homePlayer2 = Player::factory()->create([
            'team_id' => $home->id,
            'jersey_number' => 10,
            'first_name' => 'Mario',
            'last_name' => 'Local',
        ]);
        $awayPlayer1 = Player::factory()->create([
            'team_id' => $away->id,
            'jersey_number' => 7,
            'first_name' => 'Carlos',
            'last_name' => 'Visitante',
        ]);
        $awayPlayer2 = Player::factory()->create([
            'team_id' => $away->id,
            'jersey_number' => 11,
            'first_name' => 'Diego',
            'last_name' => 'Visitante',
        ]);

        return compact(
            'league',
            'tournament',
            'home',
            'away',
            'venue',
            'referee',
            'game',
            'homePlayer1',
            'homePlayer2',
            'awayPlayer1',
            'awayPlayer2',
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $context, array $overrides = []): array
    {
        return array_merge([
            'match_id' => $context['game']->id,
            'referee_id' => $context['referee']->id,
            'venue_id' => $context['venue']->id,
            'match_date' => '2026-12-01 18:00:00',
            'home_score' => 2,
            'away_score' => 1,
            'incidents' => 'Sin incidentes.',
            'status' => 'completed',
            'players' => [
                $context['homePlayer1']->id => [
                    'selected' => '1',
                    'is_starter' => '1',
                    'goals' => 1,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                ],
                $context['awayPlayer1']->id => [
                    'selected' => '1',
                    'is_starter' => '1',
                    'goals' => 1,
                    'yellow_cards' => 1,
                    'red_cards' => 0,
                ],
            ],
        ], $overrides);
    }
}
