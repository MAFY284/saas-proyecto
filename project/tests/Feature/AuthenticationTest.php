<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_login_correcto_entra_y_vuelve_al_inicio(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.com']);

        $this->post('/login', [
            'email' => 'ana@example.com',
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_el_login_incorrecto_no_inicia_sesion(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $this->from('/login')
            ->post('/login', [
                'email' => 'ana@example.com',
                'password' => 'incorrecta',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_cerrar_sesion_vuelve_al_mensaje_de_invitado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('home'));

        $this->get('/')
            ->assertOk()
            ->assertSee('No has iniciado sesión');

        $this->assertGuest();
    }

    public function test_el_registro_crea_el_usuario_e_inicia_sesion(): void
    {
        $this->post('/register', [
            'name' => 'Ana Ruiz',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Ana Ruiz',
            'first_name' => 'Ana',
            'last_name' => 'Ruiz',
            'email' => 'ana@example.com',
        ]);
    }
}
