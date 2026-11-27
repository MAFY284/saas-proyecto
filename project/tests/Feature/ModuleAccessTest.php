<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\FootballDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModuleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_matrix_saves_multiple_types_and_modules_with_independent_permissions(): void
    {
        $admin = User::factory()->create(['email' => User::BOOTSTRAP_ADMIN_EMAIL]);
        $user = User::factory()->create();
        $delegate = Role::factory()->create();
        $referee = Role::factory()->create();
        $user->roles()->attach($delegate);
        $league = League::factory()->create();

        $response = $this->actingAs($admin)->get(route('access.index'))->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'type="submit" form="access-form"'));

        $matrix = [
            $delegate->id => [
                'leagues' => ['read' => true, 'write' => false, 'create' => true, 'delete' => false],
                'players' => ['read' => false, 'write' => false, 'create' => false, 'delete' => true],
            ],
            $referee->id => [
                'referee_cards' => ['read' => true, 'write' => true, 'create' => false, 'delete' => false],
            ],
        ];
        $this->put(route('access.update'), ['matrix' => json_encode($matrix, JSON_THROW_ON_ERROR)])
            ->assertRedirect(route('access.index'));
        $this->assertDatabaseHas('permissions', ['resource' => $delegate->accessKey('players'), 'can_delete' => true]);
        $this->assertDatabaseHas('permissions', ['resource' => $referee->accessKey('referee_cards'), 'can_write' => true]);

        $invalid = $matrix;
        $invalid[$delegate->id]['leagues']['create'] = false;
        $invalid[99999] = $matrix[$referee->id];
        $this->put(route('access.update'), ['matrix' => json_encode($invalid, JSON_THROW_ON_ERROR)])
            ->assertSessionHasErrors('matrix');

        $this->actingAs($user)->get(route('leagues.index'))->assertOk()
            ->assertSee(route('leagues.create'))->assertDontSee('data-edit-url=', false)
            ->assertDontSee('id="bulk-menu"', false);
        $this->get(route('leagues.create'))->assertOk();
        $this->get(route('leagues.edit', $league))->assertForbidden();
        $this->put(route('leagues.update', $league))->assertForbidden();
        $this->delete(route('leagues.destroy', $league))->assertForbidden();
        $this->put(route('access.update'), ['matrix' => '{}'])->assertForbidden();
    }

    public function test_role_permissions_apply_only_to_their_resource_and_active_accounts(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['slug' => 'operador-admin']);
        $user->roles()->attach($role);
        $role->permissions()->attach(Permission::create([
            'resource' => 'operador-admin_leagues',
            'can_read' => true,
        ]));

        $league = League::factory()->create();
        $this->actingAs($user)->get(route('leagues.index'))
            ->assertOk()
            ->assertSee($league->name)
            ->assertDontSee('id="select-all"', false)
            ->assertDontSee('id="bulk-menu"', false)
            ->assertDontSee('data-edit-url=', false)
            ->assertDontSee(route('leagues.create'))
            ->assertDontSee(route('users.index'));
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('roles.index'))->assertForbidden();
        $this->get(route('leagues.create'))->assertForbidden();
        $this->post(route('leagues.process'))->assertForbidden();
        $this->delete(route('leagues.destroy'))->assertForbidden();

        $role->update(['is_active' => false]);
        $this->get(route('leagues.index'))->assertForbidden();

        $role->update(['is_active' => true]);
        $user->update(['is_active' => false]);
        $this->get(route('leagues.index'))->assertForbidden();
    }

    public function test_participant_matrix_changes_apply_to_an_existing_user(): void
    {
        $admin = User::factory()->create(['email' => User::BOOTSTRAP_ADMIN_EMAIL]);
        $user = User::factory()->create();
        $participant = Role::factory()->create(['slug' => 'participant:arbitro']);
        $player = Role::factory()->create(['slug' => 'jugador']);
        $standard = Role::factory()->create(['slug' => 'usuario']);
        $standard->permissions()->attach(Permission::create([
            'resource' => 'usuario_users',
            'can_read' => true,
        ]));
        $user->roles()->attach([$standard->id, $participant->id, $player->id]);

        $this->actingAs($admin)->get(route('access.index'))->assertSee('Guardar accesos');
        $this->put(route('access.update', 'arbitro'), [
            'permissions' => ['leagues' => ['read' => 1, 'write' => 1, 'create' => 1, 'delete' => 1]],
        ])->assertRedirect(route('access.index'));
        $this->put(route('access.update', 'role-'.$player->id), [
            'permissions' => ['users' => ['read' => 1]],
        ])->assertRedirect(route('access.index'));

        $this->actingAs($user)->get(route('leagues.index'))
            ->assertOk()
            ->assertSee(route('leagues.create'))
            ->assertSee('id="bulk-menu"', false);
        $this->get(route('leagues.create'))->assertOk();
        $this->get(route('users.index'))->assertOk();
        $this->get(route('users.create'))->assertForbidden();
        $this->put(route('access.update', 'arbitro'))->assertForbidden();

        $this->actingAs($admin)->put(route('access.update', 'arbitro'), [
            'permissions' => ['leagues' => ['read' => 1, 'write' => 0]],
        ])->assertRedirect(route('access.index'));

        $this->actingAs($user)->get(route('leagues.index'))->assertOk();
        $this->get(route('leagues.create'))->assertForbidden();
        $player->update(['is_active' => false]);
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_user_form_preserves_existing_roles_and_protects_administrator_actions(): void
    {
        $admin = User::factory()->create(['email' => User::BOOTSTRAP_ADMIN_EMAIL]);
        $user = User::factory()->create();
        $role = Role::factory()->create(['slug' => 'operador']);
        $secondaryRole = Role::factory()->create();
        $user->roles()->attach([$role->id, $secondaryRole->id]);
        $role->permissions()->attach(Permission::create([
            'resource' => 'operador_users',
            'can_read' => true,
            'can_write' => true,
            'can_create' => true,
        ]));
        $role->permissions()->attach(Permission::create([
            'resource' => 'operador_roles',
            'can_read' => true,
            'can_write' => true,
        ]));
        $payload = [
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'language_id' => $user->language_id,
            'time_zone' => 'America/Mexico_City',
            'is_active' => true,
        ];

        $this->actingAs($admin)->get(route('users.edit', $user))
            ->assertSee('id="is_admin"', false)
            ->assertSee('id="is_active"', false)
            ->assertDontSee('name="role_id"', false);
        $this->put(route('users.update', $user), $payload)
            ->assertRedirect(route('users.index'));
        $this->assertTrue($user->roles()->whereKey($role->id)->exists());
        $this->assertTrue($user->roles()->whereKey($secondaryRole->id)->exists());

        $this->actingAs($user)->get(route('users.create'))
            ->assertOk()
            ->assertDontSee('id="is_admin"', false)
            ->assertDontSee('name="role_id"', false);
        $this->get(route('users.index'))
            ->assertSee(route('users.create'))
            ->assertSee(route('users.edit', $user))
            ->assertDontSee(route('users.edit', $admin));
        $this->get(route('roles.index'))
            ->assertOk()
            ->assertDontSee(route('roles.create'))
            ->assertDontSee(route('roles.edit', $role))
            ->assertDontSee('id="bulk-menu"', false);
        $this->get(route('access.index'))
            ->assertOk()
            ->assertDontSee('Guardar accesos')
            ->assertDontSee('data-access-action=', false);
        $this->post(route('roles.store'))->assertForbidden();
        $this->put(route('access.update', 'arbitro'))->assertForbidden();
        $this->put(route('users.update', $user), [...$payload, 'is_admin' => true])->assertForbidden();
        $this->get(route('users.edit', $admin))->assertForbidden();
        $this->delete(route('users.destroy', $admin))->assertForbidden();
    }

    public function test_user_list_hides_write_controls_for_read_only_accounts(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['slug' => 'lector']);
        $user->roles()->attach($role);
        $role->permissions()->attach(Permission::create([
            'resource' => 'lector_users',
            'can_read' => true,
        ]));

        $this->actingAs($user)->get(route('users.index'))
            ->assertOk()
            ->assertSee($user->email)
            ->assertDontSee(route('users.create'))
            ->assertDontSee('data-edit-url=', false)
            ->assertDontSee('id="bulk-menu"', false);
        $this->get(route('users.edit', $user))->assertForbidden();
    }

    public function test_demo_admin_can_be_prepared_in_an_existing_local_installation(): void
    {
        $existing = User::factory()->create();

        $this->artisan('admin:demo')->assertSuccessful();

        $admin = User::query()->where('email', User::BOOTSTRAP_ADMIN_EMAIL)->sole();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertFalse($existing->fresh()->isAdmin());

        $admin->update(['password' => 'clave-local-actualizada']);
        $this->artisan('admin:demo')->assertSuccessful();

        $this->assertDatabaseCount('users', 2);
        $this->assertTrue(Hash::check('clave-local-actualizada', $admin->fresh()->password));
    }

    public function test_first_registered_user_has_no_administrative_access(): void
    {
        $data = [
            'name' => 'Ana Ruiz',
            'password' => 'password-seguro',
            'password_confirmation' => 'password-seguro',
        ];

        $this->post(route('register'), [...$data, 'email' => User::BOOTSTRAP_ADMIN_EMAIL])
            ->assertSessionHasErrors('email');

        $this->post(route('register'), [...$data, 'email' => 'ana@example.com'])
            ->assertRedirect(route('home'));

        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('access.index'))->assertForbidden();
    }

    public function test_editing_role_details_preserves_module_permissions(): void
    {
        $admin = User::factory()->create(['email' => User::BOOTSTRAP_ADMIN_EMAIL]);
        $role = Role::factory()->create(['slug' => 'operador']);
        $role->permissions()->attach([
            Permission::create(['resource' => 'operador_sistema', 'can_read' => true])->id,
            Permission::create(['resource' => 'operador_leagues', 'can_read' => true, 'can_write' => true])->id,
        ]);

        $this->actingAs($admin)->put(route('roles.update', $role), [
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => 'Descripción actualizada',
            'is_active' => true,
            'can_read' => true,
        ])->assertRedirect(route('roles.index'));

        $role->refresh();
        $this->assertTrue($role->hasWritePermission('leagues'));
        $this->assertFalse($role->hasReadPermission('users'));
        $this->assertFalse($role->hasWritePermission('users'));
    }

    public function test_sports_cruds_show_write_controls_only_with_write_permission(): void
    {
        $this->seed(FootballDemoSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['slug' => 'participant:delegado']));
        $this->actingAs($user);

        $modules = [
            'teams' => 'teams',
            'players' => 'players',
            'matches' => 'matches',
            'referee-cards' => 'referee_cards',
            'fields' => 'fields',
            'tournaments' => 'tournaments',
        ];

        foreach ($modules as $module => $resource) {
            $permission = Permission::create([
                'resource' => "access:delegado:{$resource}",
                'can_read' => true,
            ]);
            $id = DB::table(Permission::RESOURCE_TABLES[$resource])->value('id');
            $index = route("{$module}.index");
            $create = route("{$module}.create");
            $edit = route("{$module}.edit", $id);
            $storeAction = 'action="'.route("{$module}.store").'"';
            $updateAction = 'action="'.route("{$module}.update", $id).'"';

            $this->get($index)->assertOk()
                ->assertSee('role="search"', false)
                ->assertDontSee($create)
                ->assertDontSee('data-edit-url=', false)
                ->assertDontSee('id="bulk-menu"', false)
                ->assertDontSee('id="select-all"', false);
            $this->get($create)->assertOk()
                ->assertSee('No tienes permiso de creación')
                ->assertDontSee($storeAction, false);
            $this->get($edit)->assertOk()
                ->assertSee('No tienes permiso de escritura')
                ->assertDontSee($updateAction, false);

            $permission->update(['can_write' => true]);

            $this->get($index)->assertOk()
                ->assertDontSee($create)
                ->assertSee('data-edit-url=', false)
                ->assertDontSee('id="bulk-menu"', false);

            $permission->update(['can_create' => true, 'can_delete' => true]);

            $this->get($index)->assertOk()
                ->assertSee($create)
                ->assertSee('data-edit-url=', false)
                ->assertSee('id="bulk-menu"', false)
                ->assertSee('id="select-all"', false);
            $this->get($create)->assertOk()->assertSee($storeAction, false);
            $this->get($edit)->assertOk()->assertSee($updateAction, false);
        }
    }
}
