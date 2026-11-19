<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\League;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->get('q', '')), 0, 100);

        $roles = Role::query()
            ->with(['permissions'])
            ->where('slug', 'not like', 'participant%')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $dateLike = '%'.str_replace(['/', '.'], '-', addcslashes($search, '%_\\')).'%';

                $query->where(function ($query) use ($like, $dateLike) {
                    $query->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('id', 'like', $like)
                        ->orWhere('created_at', 'like', $like)
                        ->orWhere('created_at', 'like', $dateLike);
                });
            })
            ->latest('id')
            ->paginate(8)
            ->withQueryString();

        return view('roles.index', compact('roles', 'search'));
    }

    public function create()
    {
        $role = new Role([
            'is_active' => true,
        ]);

        return view('roles.create', [
            'role' => $role,
            'leagues' => League::query()->orderBy('name')->get(),
            'availableResources' => Permission::RESOURCES,
            'rolePermissions' => [],
            'hasReadPermission' => false,
            'hasWritePermission' => false,
        ]);
    }

    public function store(Request $request)
    {
        if (auth()->check() && ! auth()->user()->canWrite()) {
            return back()->with('error', 'No tienes permisos de escritura para crear roles.');
        }

        $data = $this->validatedData($request);

        $role = new Role;
        $role->name = $data['name'];
        $role->slug = $data['slug'];
        $role->description = $data['description'] ?? null;
        $role->is_active = $data['is_active'];

        if (Schema::hasColumn('roles', 'league_id')) {
            $leagueId = $data['league_id'] ?? $request->input('league_id');
            if (! $leagueId) {
                $leagueId = League::query()->value('id');
                if (! $leagueId) {
                    $countryId = Country::query()->value('id') ?? Country::create(['name' => 'México', 'iso2_code' => 'MX'])->id;
                    $currencyId = Currency::query()->value('id') ?? Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$'])->id;
                    $defaultLeague = League::create([
                        'name' => 'Liga General',
                        'slug' => 'liga-general',
                        'country_id' => $countryId,
                        'currency_id' => $currencyId,
                        'time_zone' => 'UTC',
                        'is_active' => true,
                    ]);
                    $leagueId = $defaultLeague->id;
                }
            }
            $role->league_id = $leagueId;
        }

        $role->save();

        $this->syncRolePermissions($role, $request);

        return redirect()
            ->route('roles.index')
            ->with('status', 'Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');

        $rolePermissions = [];
        foreach ($role->permissions as $permission) {
            $rolePermissions[$permission->resource] = [
                'read' => (bool) $permission->can_read,
                'write' => (bool) $permission->can_write,
            ];
        }

        return view('roles.edit', [
            'role' => $role,
            'leagues' => League::query()->orderBy('name')->get(),
            'availableResources' => Permission::RESOURCES,
            'rolePermissions' => $rolePermissions,
            'hasReadPermission' => $role->hasReadPermission(),
            'hasWritePermission' => $role->hasWritePermission(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if (auth()->check() && ! auth()->user()->canWrite()) {
            return back()->with('error', 'No tienes permisos de escritura para modificar roles.');
        }

        $data = $this->validatedData($request, $role);

        $role->name = $data['name'];
        $role->slug = $data['slug'];
        $role->description = $data['description'] ?? null;
        $role->is_active = $data['is_active'];

        if (Schema::hasColumn('roles', 'league_id') && ($data['league_id'] ?? $request->input('league_id'))) {
            $role->league_id = $data['league_id'] ?? $request->input('league_id');
        }

        $role->save();

        $this->syncRolePermissions($role, $request);

        return redirect()
            ->route('roles.index')
            ->with('status', 'Rol actualizado correctamente.');
    }

    public function destroy(Request $request, ?Role $role = null)
    {
        if (auth()->check() && ! auth()->user()->canWrite()) {
            return back()->with('error', 'No tienes permisos de escritura para eliminar roles.');
        }

        $ids = collect($request->input('ids', []))
            ->when($role?->id, fn ($ids, $id) => $ids->push($id))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecciona al menos un rol.');
        }

        $roles = Role::query()
            ->whereIn('id', $ids)
            ->get();

        // Verificar si hay usuarios asignados (solo si la columna user_id existe en user_roles)
        try {
            $rolesConUsuarios = Role::query()
                ->whereIn('id', $ids)
                ->withCount('users')
                ->get()
                ->filter(fn ($r) => $r->users_count > 0);

            if ($rolesConUsuarios->isNotEmpty()) {
                $names = $rolesConUsuarios->pluck('name')->implode(', ');
                return back()->with('error', "No se pueden eliminar los roles ({$names}) porque están asignados a uno o más usuarios.");
            }
        } catch (\Exception $e) {
            // La tabla user_roles no tiene la estructura esperada; continuamos sin validar.
        }

        // Desvincular permisos antes de borrar para evitar errores de clave foránea
        $roles->each(fn (Role $r) => $r->permissions()->detach());

        $deleted = Role::query()
            ->whereIn('id', $roles->modelKeys())
            ->delete();

        if ($deleted === 0) {
            return back()->with('error', 'No se eliminó ningún rol.');
        }

        return redirect()
            ->route('roles.index')
            ->with('status', $deleted === 1 ? 'Rol eliminado.' : "{$deleted} roles eliminados.");
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:roles,id'],
        ], [
            'ids.required' => 'Selecciona al menos un rol.',
            'ids.min' => 'Selecciona al menos un rol.',
        ]);

        $count = count($data['ids']);

        return back()->with(
            'status',
            $count === 1
                ? 'Proceso ejecutado para 1 rol.'
                : "Proceso ejecutado para {$count} roles."
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Role $role = null): array
    {
        $uniqueName = Rule::unique('roles', 'name');
        $uniqueSlug = Rule::unique('roles', 'slug');

        if ($role) {
            $uniqueName->ignore($role->id);
            $uniqueSlug->ignore($role->id);
        }

        $validated = $request->validate([
            'league_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:100', $uniqueName],
            'slug' => ['nullable', 'string', 'max:100', $uniqueSlug],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'can_read' => ['nullable', 'boolean'],
            'can_write' => ['nullable', 'boolean'],
            'type_read' => ['nullable', 'boolean'],
            'type_write' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con este nombre.',
            'slug.unique' => 'Ya existe un rol con este slug.',
        ]);

        $name = trim($validated['name']);
        $slug = trim((string) ($validated['slug'] ?? ''));

        if ($slug === '') {
            $slug = Str::slug($name);
        } else {
            $slug = Str::slug($slug);
        }

        $validated['name'] = $name;
        $validated['slug'] = $slug;
        $validated['is_active'] = (bool) $validated['is_active'];

        return $validated;
    }

    /**
     * Sincroniza permisos generales (Lectura / Escritura) por rol.
     * Cada rol tiene sus propios registros de permisos usando el slug del rol como prefijo
     * en el nombre del recurso (ej. "admin_sistema", "usuario_sistema"), lo que evita
     * que actualizar un rol modifique los permisos de otro.
     */
    private function syncRolePermissions(Role $role, Request $request): void
    {
        $canRead  = $request->boolean('can_read')  || $request->boolean('type_read');
        $canWrite = $request->boolean('can_write') || $request->boolean('type_write');
        $submittedPermissions = (array) $request->input('permissions', []);

        // Prefijo único por rol para aislar sus permisos
        $prefix = $role->slug . '_';

        $permissionIds = [];

        // 1. Permiso general del sistema (modo simplificado Administrador / Usuario)
        $systemResource = $prefix . 'sistema';
        $systemPerm = Permission::updateOrCreate(
            ['resource' => $systemResource],
            [
                'can_read'   => $canRead ? 1 : 0,
                'can_write'  => $canWrite ? 1 : 0,
                'can_create' => $canWrite ? 1 : 0,
                'can_delete' => $canWrite ? 1 : 0,
            ]
        );
        $permissionIds[] = $systemPerm->id;

        // 2. Permisos de recursos base (guardado para uso futuro con permisos granulares)
        foreach (Permission::RESOURCES as $resourceKey => $resourceLabel) {
            $resourceName = $prefix . $resourceKey;

            // Si hay desglose granular para este recurso, usa esos valores
            $hasModularRead  = !empty($submittedPermissions[$resourceKey]['read']);
            $hasModularWrite = !empty($submittedPermissions[$resourceKey]['write']);

            // Si hay desglose, usa el desglose; si no, hereda los flags globales
            $read  = $hasModularRead  || ($canRead  && !array_key_exists($resourceKey, $submittedPermissions));
            $write = $hasModularWrite || ($canWrite && !array_key_exists($resourceKey, $submittedPermissions));

            if ($read || $write) {
                $perm = Permission::updateOrCreate(
                    ['resource' => $resourceName],
                    [
                        'can_read'   => $read ? 1 : 0,
                        'can_write'  => $write ? 1 : 0,
                        'can_create' => $write ? 1 : 0,
                        'can_delete' => $write ? 1 : 0,
                    ]
                );
                $permissionIds[] = $perm->id;
            }
        }

        $role->permissions()->sync(array_unique($permissionIds));
    }
}
