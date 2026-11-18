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

    public function test_usuario_autenticado_ve_roles_administrador_y_usuario(): void
    {
        $actor = $this->createAuthenticatedUser();

        $admin = Role::create([
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Acceso total',
            'is_active' => true,
        ]);
        $readWritePerm = Permission::create([
            'resource' => 'sistema',
            'can_read' => true,
            'can_write' => true,
        ]);
        $admin->permissions()->attach($readWritePerm->id);

        $user = Role::create([
            'name' => 'Usuario',
            'slug' => 'usuario',
            'description' => 'Usuario estándar',
            'is_active' => true,
        ]);
        $readPerm = Permission::create([
            'resource' => 'sistema',
            'can_read' => true,
            'can_write' => false,
        ]);
        $user->permissions()->attach($readPerm->id);

        $response = $this->actingAs($actor)->get(route('roles.index'));

        $response->assertOk();
        $response->assertSee('Roles y Permisos');
        $response->assertSee('Administrador');
        $response->assertSee('admin');
        $response->assertSee('Usuario');
        $response->assertSee('usuario');

        $this->assertTrue($admin->hasReadPermission());
        $this->assertTrue($admin->hasWritePermission());
        $this->assertTrue($user->hasReadPermission());
        $this->assertFalse($user->hasWritePermission());
    }

    public function test_se_puede_crear_un_rol_con_permisos_de_lectura_por_casilla(): void
    {
        $actor = $this->createAuthenticatedUser();

        $response = $this->actingAs($actor)->post(route('roles.store'), [
            'name' => 'Auditor',
            'slug' => 'auditor',
            'description' => 'Rol únicamente de lectura para auditoría.',
            'is_active' => '1',
            'can_read' => '1',
            'can_write' => '0',
        ]);

        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('status', 'Rol creado correctamente.');

        $role = Role::query()->where('slug', 'auditor')->first();
        $this->assertNotNull($role);
        $this->assertSame('Auditor', $role->name);
        $this->assertTrue($role->is_active);

        $this->assertTrue($role->hasReadPermission());
        $this->assertFalse($role->hasWritePermission());
    }

    public function test_se_puede_crear_un_rol_con_permisos_de_escritura_por_casilla(): void
    {
        $actor = $this->createAuthenticatedUser();

        $response = $this->actingAs($actor)->post(route('roles.store'), [
            'name' => 'Editor',
            'slug' => 'editor',
            'description' => 'Rol con permisos de escritura.',
            'is_active' => '1',
            'can_read' => '0',
            'can_write' => '1',
        ]);

        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('status', 'Rol creado correctamente.');

        $role = Role::query()->where('slug', 'editor')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasWritePermission());
    }

    public function test_se_puede_crear_un_rol_con_permisos_de_lectura_y_escritura(): void
    {
        $actor = $this->createAuthenticatedUser();

        $response = $this->actingAs($actor)->post(route('roles.store'), [
            'name' => 'Super Administrador',
            'slug' => 'super-admin',
            'description' => 'Rol con ambos permisos.',
            'is_active' => '1',
            'can_read' => '1',
            'can_write' => '1',
        ]);

        $response->assertRedirect(route('roles.index'));

        $role = Role::query()->where('slug', 'super-admin')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasReadPermission());
        $this->assertTrue($role->hasWritePermission());
    }

    public function test_compatibilidad_futura_con_desglose_por_modulos(): void
    {
        $actor = $this->createAuthenticatedUser();

        $response = $this->actingAs($actor)->post(route('roles.store'), [
            'name' => 'Gestor Especial',
            'slug' => 'gestor-especial',
            'description' => 'Permisos por módulos guardados para el futuro',
            'is_active' => '1',
            'permissions' => [
                'leagues' => ['read' => '1', 'write' => '1'],
                'users' => ['read' => '1', 'write' => '0'],
            ],
        ]);

        $response->assertRedirect(route('roles.index'));

        $role = Role::query()->where('slug', 'gestor-especial')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasReadPermission('leagues'));
        $this->assertTrue($role->hasWritePermission('leagues'));
        $this->assertTrue($role->hasReadPermission('users'));
        $this->assertFalse($role->hasWritePermission('users'));
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

    public function test_se_puede_editar_un_rol_y_sus_permisos(): void
    {
        $actor = $this->createAuthenticatedUser();

        $role = Role::create([
            'name' => 'Mesa de Control',
            'slug' => 'mesa-control',
            'description' => 'Original',
            'is_active' => true,
        ]);

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
        $this->assertTrue($role->hasReadPermission());
        $this->assertTrue($role->hasWritePermission());
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
            'language_id' => $language->id,
        ]);
    }
}
