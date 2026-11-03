<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\League;
use App\Models\MatchReport;
use App\Models\Referee;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_no_entra_al_crud_de_partidos(): void
    {
        $this->get(route('matches.index'))->assertRedirect(route('login'));
        $this->get(route('matches.create'))->assertRedirect(route('login'));
    }

    public function test_se_puede_crear_un_partido_con_datos_validos(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();

        $this->actingAs($actor)
            ->post(route('matches.store'), $this->payload($context, [
                'home_goals' => 2,
                'away_goals' => 1,
                'matchday' => 3,
                'status' => 'finished',
                'notes' => 'Partido de prueba.',
            ]))
            ->assertRedirect(route('matches.index'))
            ->assertSessionHas('status', 'Partido creado.');

        $game = Game::query()->first();

        $this->assertNotNull($game);
        $this->assertSame($context['tournament']->id, $game->tournament_id);
        $this->assertSame($context['home']->id, $game->home_team_id);
        $this->assertSame($context['away']->id, $game->away_team_id);
        $this->assertSame($context['venue']->id, $game->venue_id);
        $this->assertSame($context['referee']->id, $game->referee_id);
        $this->assertSame(2, $game->home_goals);
        $this->assertSame(1, $game->away_goals);
        $this->assertSame('finished', $game->status);
    }

    public function test_crear_partido_rechaza_cada_restriccion_de_campo(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();

        $cases = [
            ['tournament_id', ['tournament_id' => 99999], 'El torneo no es válido.'],
            ['home_team_id', ['home_team_id' => 99999], 'El equipo local no es válido.'],
            ['away_team_id', ['away_team_id' => 99999], 'El equipo visitante no es válido.'],
            ['away_team_id', ['away_team_id' => $context['home']->id], 'El equipo visitante no puede ser el mismo que el local.'],
            ['venue_id', ['venue_id' => 99999], 'La sede no es válida.'],
            ['referee_id', ['referee_id' => 99999], 'El árbitro no es válido.'],
            ['match_date', ['match_date' => ''], 'La fecha del partido es obligatoria.'],
            ['match_date', ['match_date' => 'no-es-fecha'], 'La fecha del partido no es válida.'],
            ['home_goals', ['home_goals' => -1], 'Los goles del local no pueden ser negativos.'],
            ['away_goals', ['away_goals' => -1], 'Los goles del visitante no pueden ser negativos.'],
            ['matchday', ['matchday' => 0], 'La jornada debe ser mayor que cero.'],
            ['status', ['status' => 'no-existe'], 'El estado no es válido.'],
            ['notes', ['notes' => str_repeat('a', 2001)], 'Las notas no pueden superar 2000 caracteres.'],
        ];

        foreach ($cases as [$field, $override, $message]) {
            $before = Game::query()->count();

            $response = $this->actingAs($actor)
                ->from(route('matches.create'))
                ->post(route('matches.store'), $this->payload($context, $override));

            $response->assertRedirect(route('matches.create'));
            $response->assertSessionHasErrors([$field => $message]);

            $this->assertSame(
                $before,
                Game::query()->count(),
                "Se creó un partido con {$field} inválido."
            );
        }
    }

    public function test_se_puede_editar_un_partido(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();
        $newAway = Team::factory()->create([
            'league_id' => $context['league']->id,
            'name' => 'Nuevo Visitante',
            'slug' => 'nuevo-visitante',
        ]);
        $game = Game::factory()->create([
            'tournament_id' => $context['tournament']->id,
            'home_team_id' => $context['home']->id,
            'away_team_id' => $context['away']->id,
            'venue_id' => $context['venue']->id,
            'referee_id' => $context['referee']->id,
            'status' => 'scheduled',
        ]);

        $this->actingAs($actor)
            ->put(route('matches.update', $game), $this->payload($context, [
                'away_team_id' => $newAway->id,
                'home_goals' => 3,
                'away_goals' => 0,
                'status' => 'finished',
            ]))
            ->assertRedirect(route('matches.index'))
            ->assertSessionHas('status', 'Partido actualizado.');

        $game->refresh();

        $this->assertSame($newAway->id, $game->away_team_id);
        $this->assertSame(3, $game->home_goals);
        $this->assertSame(0, $game->away_goals);
        $this->assertSame('finished', $game->status);
    }

    public function test_se_puede_eliminar_un_partido_sin_acta(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();
        $game = Game::factory()->create([
            'tournament_id' => $context['tournament']->id,
            'home_team_id' => $context['home']->id,
            'away_team_id' => $context['away']->id,
        ]);

        $this->actingAs($actor)
            ->delete(route('matches.destroy'), ['ids' => [$game->id]])
            ->assertRedirect(route('matches.index'))
            ->assertSessionHas('status', 'Partido eliminado.');

        $this->assertModelMissing($game);
    }

    public function test_no_se_puede_eliminar_un_partido_con_acta(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();
        $game = Game::factory()->create([
            'tournament_id' => $context['tournament']->id,
            'home_team_id' => $context['home']->id,
            'away_team_id' => $context['away']->id,
        ]);
        MatchReport::factory()->create(['match_id' => $game->id]);

        $this->actingAs($actor)
            ->delete(route('matches.destroy'), ['ids' => [$game->id]])
            ->assertSessionHas('error');

        $this->assertModelExists($game);
    }

    public function test_el_index_filtra_por_torneo_y_estado(): void
    {
        $actor = User::factory()->create();
        $context = $this->context();
        $otherTournament = Tournament::factory()->create(['league_id' => $context['league']->id, 'name' => 'Otro Torneo']);
        $otherHome = Team::factory()->create(['league_id' => $context['league']->id, 'name' => 'Otro Local', 'slug' => 'otro-local']);
        $otherAway = Team::factory()->create(['league_id' => $context['league']->id, 'name' => 'Otro Visitante', 'slug' => 'otro-visitante']);

        Game::factory()->create([
            'tournament_id' => $context['tournament']->id,
            'home_team_id' => $context['home']->id,
            'away_team_id' => $context['away']->id,
            'status' => 'finished',
        ]);

        Game::factory()->create([
            'tournament_id' => $otherTournament->id,
            'home_team_id' => $otherHome->id,
            'away_team_id' => $otherAway->id,
            'status' => 'scheduled',
        ]);

        $this->actingAs($actor)
            ->get(route('matches.index', [
                'tournament_id' => $context['tournament']->id,
                'status' => 'finished',
            ]))
            ->assertOk()
            ->assertSee($context['home']->name)
            ->assertDontSee($otherHome->name);
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

        return compact('league', 'tournament', 'home', 'away', 'venue', 'referee');
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $context, array $overrides = []): array
    {
        return array_merge([
            'tournament_id' => $context['tournament']->id,
            'home_team_id' => $context['home']->id,
            'away_team_id' => $context['away']->id,
            'venue_id' => $context['venue']->id,
            'referee_id' => $context['referee']->id,
            'match_date' => '2026-12-01 18:00:00',
            'home_goals' => '',
            'away_goals' => '',
            'matchday' => 1,
            'status' => 'scheduled',
            'notes' => '',
        ], $overrides);
    }
}
