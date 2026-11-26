<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Route::has('roles.index')) {
            require base_path('routes/roles.php');
        }
    }

    public function test_un_invitado_no_entra_al_crud_de_roles(): void
    {
        $this->get(route('roles.index'))->assertRedirect(route('login'));
        $this->get(route('roles.create'))->assertRedirect(route('login'));
    }

    public function test_solo_los_tipos_de_participante_aparecen_en_roles_acceso_y_usuarios(): void
    {
        $actor = $this->createAuthenticatedUser();
        $legacy = Role::factory()->create(['name' => 'Árbitro', 'slug' => 'participant:arbitro']);
        $systemRole = Role::factory()->create(['name' => 'Administrador', 'slug' => 'admin']);
        Role::factory()->create(['name' => 'Usuario estándar', 'slug' => 'usuario']);

        $this->actingAs($actor)->get(route('roles.create'))
            ->assertOk()->assertSee('name="name"', false)->assertDontSee('handleRolePreset');
        $this->post(route('roles.store'), ['name' => 'Delegada deportiva', 'is_active' => true])
            ->assertRedirect(route('roles.index'));

        foreach (['roles.index', 'access.index', 'users.create'] as $route) {
            $this->get(route($route))->assertOk()
                ->assertSee('Delegada deportiva')
                ->assertSee($legacy->name)
                ->assertDontSee('Usuario estándar');
        }

        $this->get(route('roles.edit', $systemRole))->assertNotFound();
        $this->put(route('access.update', 'role-'.$systemRole->id))->assertNotFound();
    }

    public function test_los_nombres_y_slugs_de_sistema_estan_reservados(): void
    {
        $this->actingAs($this->createAuthenticatedUser());

        $this->post(route('roles.store'), ['name' => 'ADMINISTRADOR', 'is_active' => true])
            ->assertSessionHasErrors(['name', 'slug']);
        $this->post(route('roles.store'), ['name' => 'Nuevo tipo', 'slug' => 'USUARIO', 'is_active' => true])
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('roles', 0);
    }

    public function test_slug_se_genera_automaticamente_si_no_se_provee(): void
    {
        $actor = $this->createAuthenticatedUser();

        $this->actingAs($actor)->post(route('roles.store'), [
            'name' => 'Operador Principal',
            'slug' => '',
            'description' => 'Prueba sin slug manual',
            'is_active' => '1',
            'can_read' => '1',
        ]);

        $role = Role::query()->where('name', 'Operador Principal')->first();
        $this->assertNotNull($role);
        $this->assertSame('operador-principal', $role->slug);
    }

    public function test_validaciones_de_nombre_obligatorio_y_unicidad(): void
    {
        $actor = $this->createAuthenticatedUser();

        Role::create([
            'name' => 'Arbitro',
            'slug' => 'arbitro',
            'is_active' => true,
        ]);

        $response = $this->actingAs($actor)->post(route('roles.store'), [
            'name' => '',
            'is_active' => '1',
        ]);
        $response->assertSessionHasErrors(['name']);

        $responseDuplicate = $this->actingAs($actor)->post(route('roles.store'), [
            'name' => 'Arbitro',
            'slug' => 'arbitro',
            'is_active' => '1',
        ]);
        $responseDuplicate->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_renombrar_un_tipo_conserva_el_acceso_guardado_anteriormente(): void
    {
        $actor = $this->createAuthenticatedUser();

        $role = Role::create([
            'name' => 'Mesa de Control',
            'slug' => 'mesa-control',
            'description' => 'Original',
            'is_active' => true,
        ]);

        Permission::create(['resource' => 'access:mesa-control:leagues', 'can_read' => true, 'can_write' => true]);

        $response = $this->actingAs($actor)->get(route('roles.edit', $role));
        $response->assertOk();
        $response->assertSee('Mesa de Control');

        $updateResponse = $this->actingAs($actor)->put(route('roles.update', $role), [
            'name' => 'Mesa de Control Modificada',
            'slug' => 'mesa-control-modificada',
            'description' => 'Actualizada con escritura',
            'is_active' => '1',
            'can_read' => '1',
            'can_write' => '1',
        ]);

        $updateResponse->assertRedirect(route('roles.index'));
        $role->refresh();

        $this->assertSame('Mesa de Control Modificada', $role->name);
        $this->assertSame('mesa-control-modificada', $role->slug);
        $this->assertDatabaseHas('permissions', [
            'resource' => $role->accessKey('leagues'),
            'can_read' => true,
            'can_write' => true,
        ]);
    }

    public function test_se_puede_eliminar_un_rol(): void
    {
        $actor = $this->createAuthenticatedUser();

        $role = Role::create([
            'name' => 'Rol Para Borrar',
            'slug' => 'rol-para-borrar',
            'is_active' => true,
        ]);

        $response = $this->actingAs($actor)->delete(route('roles.destroy', $role));
        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('status', 'Rol eliminado.');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_busqueda_de_roles(): void
    {
        $actor = $this->createAuthenticatedUser();

        Role::create(['name' => 'Encargado Canchas', 'slug' => 'encargado-canchas', 'is_active' => true]);
        Role::create(['name' => 'Director Técnico', 'slug' => 'director-tecnico', 'is_active' => true]);

        $response = $this->actingAs($actor)->get(route('roles.index', ['q' => 'Canchas']));
        $response->assertOk();
        $response->assertSee('Encargado Canchas');
        $response->assertDontSee('Director Técnico');
    }

    public function test_ejecutar_proceso_masivo(): void
    {
        $actor = $this->createAuthenticatedUser();

        $role1 = Role::create(['name' => 'Rol 1', 'slug' => 'rol-1', 'is_active' => true]);
        $role2 = Role::create(['name' => 'Rol 2', 'slug' => 'rol-2', 'is_active' => true]);

        $response = $this->actingAs($actor)->post(route('roles.process'), [
            'ids' => [$role1->id, $role2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Proceso ejecutado para 2 roles.');
    }

    private function createAuthenticatedUser(): User
    {
        $language = Language::firstOrCreate(
            ['iso_code' => 'es'],
            ['name' => 'Español', 'native_name' => 'Español']
        );

        return User::factory()->create([
            'email' => User::BOOTSTRAP_ADMIN_EMAIL,
            'language_id' => $language->id,
        ]);
    }
}
