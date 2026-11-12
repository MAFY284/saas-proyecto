<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_no_entra_al_crud_de_usuarios(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
        $this->get(route('users.create'))->assertRedirect(route('login'));
    }

    public function test_se_puede_crear_un_usuario_con_los_campos_de_la_tabla(): void
    {
        $actor = User::factory()->create();
        $language = Language::factory()->create();

        $this->actingAs($actor)
            ->post(route('users.store'), $this->userPayload([
                'language_id' => $language->id,
                'email' => 'ana.ruiz@example.com',
                'phone' => '5551234567',
                'is_active' => '1',
            ]))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('status', 'Usuario creado.');

        $created = User::query()->where('email', 'ana.ruiz@example.com')->first();

        $this->assertNotNull($created);
        $this->assertSame('Ana', $created->first_name);
        $this->assertSame('Ruiz', $created->last_name);
        $this->assertSame('Ana Ruiz', $created->name);
        $this->assertSame('5551234567', $created->phone);
        $this->assertSame('America/Mexico_City', $created->time_zone);
        $this->assertSame($language->id, $created->language_id);
        $this->assertTrue($created->is_active);
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_crear_usuario_rechaza_cada_restriccion_de_campo(): void
    {
        $actor = User::factory()->create();
        User::factory()->create(['email' => 'ana.ruiz@example.com']);

        $tooLongName = str_repeat('a', 51);

        $cases = [
            ['first_name', ['first_name' => ''], 'El nombre es obligatorio.'],
            ['last_name', ['last_name' => ''], 'El apellido es obligatorio.'],
            ['name', ['name' => ''], 'El nombre visible es obligatorio.'],
            ['first_name', ['first_name' => 'A'], 'El nombre debe tener al menos 2 caracteres.'],
            ['last_name', ['last_name' => 'A'], 'El apellido debe tener al menos 2 caracteres.'],
            ['name', ['name' => 'A'], 'El nombre visible debe tener al menos 2 caracteres.'],
            ['first_name', ['first_name' => $tooLongName], 'El nombre no puede superar 50 caracteres.'],
            ['last_name', ['last_name' => $tooLongName], 'El apellido no puede superar 50 caracteres.'],
            ['name', ['name' => $tooLongName], 'El nombre visible no puede superar 50 caracteres.'],
            ['email', ['email' => ''], 'El email es obligatorio.'],
            ['email', ['email' => 'no-es-correo'], 'El email no es válido.'],
            ['email', ['email' => 'ana.ruiz@example.com'], 'El email ya está en uso.'],
            ['phone', ['phone' => str_repeat('1', 51)], 'El teléfono no puede superar 50 caracteres.'],
            ['time_zone', ['time_zone' => ''], 'La zona horaria es obligatoria.'],
            ['time_zone', ['time_zone' => 'No/Existe'], 'La zona horaria no es válida.'],
            ['is_active', ['is_active' => null], null],
            ['is_active', ['is_active' => 'quizas'], null],
            ['password', ['password' => ''], 'El password es obligatorio.'],
            ['password', ['password' => 'corta'], 'El password debe tener al menos 8 caracteres.'],
            ['language_id', ['language_id' => ''], 'El idioma es obligatorio.'],
            ['language_id', ['language_id' => 99999], 'El idioma no es válido.'],
            ['avatar', ['avatar' => UploadedFile::fake()->create('notas.pdf', 20, 'application/pdf')], 'El archivo debe ser una imagen.'],
            ['avatar', ['avatar' => UploadedFile::fake()->image('foto.jpg')->size(3000)], 'La imagen no puede superar 2 MB.'],
        ];

        foreach ($cases as [$field, $override, $message]) {
            $before = User::query()->count();

            $response = $this->actingAs($actor)
                ->from(route('users.create'))
                ->post(route('users.store'), $this->userPayload($override));

            $response->assertRedirect(route('users.create'));
            $response->assertSessionHasErrors(
                $message === null ? [$field] : [$field => $message]
            );

            $this->assertSame(
                $before,
                User::query()->count(),
                "Se creó un usuario con {$field} inválido."
            );
        }
    }

    public function test_editar_sin_password_conserva_la_contrasena_y_actualiza_los_demas_campos(): void
    {
        $actor = User::factory()->create();
        $user = User::factory()->create([
            'email' => 'ana.ruiz@example.com',
            'phone' => '111',
            'is_active' => true,
        ]);
        $hash = $user->password;

        $this->actingAs($actor)
            ->put(route('users.update', $user), $this->userPayload([
                'email' => 'ana.ruiz@example.com',
                'phone' => '5559999',
                'is_active' => '0',
                'password' => '',
            ]))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('status', 'Usuario actualizado.');

        $user->refresh();

        $this->assertSame($hash, $user->password);
        $this->assertSame('5559999', $user->phone);
        $this->assertFalse($user->is_active);
    }

    public function test_editar_rechaza_correos_invalidos_y_duplicados(): void
    {
        $actor = User::factory()->create();
        $user = User::factory()->create(['email' => 'ana.ruiz@example.com']);
        $other = User::factory()->create(['email' => 'otro@example.com']);

        foreach (['ana@gmail', $other->email] as $email) {
            $this->actingAs($actor)
                ->from(route('users.edit', $user))
                ->put(route('users.update', $user), $this->userPayload([
                    'email' => $email,
                    'password' => '',
                ]))
                ->assertRedirect(route('users.edit', $user))
                ->assertSessionHasErrors('email');
        }
    }

    public function test_se_puede_eliminar_otro_usuario_pero_no_la_propia_cuenta(): void
    {
        $actor = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($actor)
            ->delete(route('users.destroy'), ['ids' => [$actor->id]])
            ->assertSessionHas('error');

        $this->assertModelExists($actor);

        $this->actingAs($actor)
            ->delete(route('users.destroy'), ['ids' => [$other->id]])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('status', 'Usuario eliminado.');

        $this->assertModelMissing($other);
        $this->assertModelExists($actor);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function userPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Ana',
            'last_name' => 'Ruiz',
            'name' => 'Ana Ruiz',
            'email' => 'ana.nueva@example.com',
            'phone' => '',
            'language_id' => Language::factory()->create()->id,
            'time_zone' => 'America/Mexico_City',
            'is_active' => '1',
            'password' => 'password123',
        ], $overrides);
    }
}
