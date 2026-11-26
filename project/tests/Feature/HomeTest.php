<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_inicio_indica_que_no_hay_sesion(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('No has iniciado sesión')
            ->assertDontSee('Usuarios')
            ->assertDontSee('Partidos')
            ->assertDontSee('Actas');
    }

    public function test_el_inicio_saluda_al_usuario_autenticado(): void
    {
        $user = User::factory()->create(['name' => 'Ana Ruiz']);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Has iniciado sesión como Ana Ruiz')
            ->assertDontSee('Usuarios')
            ->assertDontSee(route('leagues.index'))
            ->assertDontSee('Administración')
            ->assertDontSee('Gestión')
            ->assertDontSee('Partidos')
            ->assertDontSee('Actas')
            ->assertDontSee('Jugadores')
            ->assertSee('Cerrar sesión');
    }

    public function test_la_ruta_dashboard_redirige_al_inicio(): void
    {
        $this->get('/dashboard')->assertRedirect('/');
    }

    public function test_el_menu_de_gestion_respeta_la_lectura_del_tipo_de_participante(): void
    {
        $admin = User::factory()->create(['email' => User::BOOTSTRAP_ADMIN_EMAIL]);
        $delegate = User::factory()->create();
        $delegate->roles()->attach(Role::factory()->create(['slug' => 'participant:delegado']));

        $this->actingAs($admin)->get(route('access.index'))
            ->assertOk()
            ->assertSee('data-access-resource="players"', false)
            ->assertSee('data-access-resource="referee_cards"', false)
            ->assertSee('data-access-action="create"', false)
            ->assertSee('data-access-action="delete"', false);

        $this->put(route('access.update', 'delegado'), [
            'permissions' => [
                'matches' => ['read' => 1],
                'fields' => ['read' => 1],
                'teams' => ['write' => 1],
                'players' => ['read' => 1],
                'referee_cards' => ['write' => 1],
            ],
        ])->assertRedirect(route('access.index'));

        $this->actingAs($delegate)->get(route('home'))
            ->assertOk()
            ->assertSee(route('matches.index'))
            ->assertSee(route('fields.index'))
            ->assertDontSee(route('teams.index'))
            ->assertDontSee(route('tournaments.index'))
            ->assertDontSee(route('referee-cards.index'))
            ->assertSee(route('players.index'));

        $this->get(route('teams.index'))->assertOk();

        $this->actingAs($admin)->put(route('access.update', 'delegado'), [
            'permissions' => ['referee_cards' => ['read' => 1]],
        ])->assertRedirect(route('access.index'));

        $this->actingAs($delegate)->get(route('home'))
            ->assertOk()
            ->assertSee(route('referee-cards.index'))
            ->assertDontSee(route('players.index'));
    }
}
