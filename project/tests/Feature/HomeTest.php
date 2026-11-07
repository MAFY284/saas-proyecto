<?php

namespace Tests\Feature;

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
            ->assertSee('Usuarios')
            ->assertSee('Gestión')
            ->assertSee('Partidos')
            ->assertSee('Actas')
            ->assertSee('Cerrar sesión');
    }

    public function test_la_ruta_dashboard_redirige_al_inicio(): void
    {
        $this->get('/dashboard')->assertRedirect('/');
    }
}
