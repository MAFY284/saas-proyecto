<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\League;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->get('q', '')), 0, 100);

        $roles = Role::query()
            ->participantTypes()
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

        return view('roles.create', compact('role'));
    }

    public function store(Request $request)
    {
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

        DB::transaction(function () use ($role) {
            $role->save();
            $this->copyLegacyAccess($role, $role, inherit: false);
        });

        return redirect()
            ->route('roles.index')
            ->with('status', 'Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        abort_unless($role->isParticipantType(), 404);

        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        abort_unless($role->isParticipantType(), 404);

        $data = $this->validatedData($request, $role);
        $previousRole = clone $role;

        $role->name = $data['name'];
        $role->slug = $data['slug'];
        $role->description = $data['description'] ?? null;
        $role->is_active = $data['is_active'];

        if (Schema::hasColumn('roles', 'league_id') && ($data['league_id'] ?? $request->input('league_id'))) {
            $role->league_id = $data['league_id'] ?? $request->input('league_id');
        }

        DB::transaction(function () use ($role, $previousRole) {
            if ($role->isDirty('slug') || $role->participantType() !== $previousRole->participantType()) {
                $this->copyLegacyAccess($role, $previousRole);
            }

            $role->save();
        });

        return redirect()
            ->route('roles.index')
            ->with('status', 'Rol actualizado correctamente.');
    }

    public function destroy(Request $request, ?Role $role = null)
    {
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

        abort_if($roles->contains(fn (Role $item) => ! $item->isParticipantType()), 404);

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

        abort_unless(Role::query()->participantTypes()->whereKey($data['ids'])->count() === count(array_unique($data['ids'])), 404);

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
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'El nombre del tipo es obligatorio.',
        ]);

        $slug = $validated['slug'] ?? $role?->slug ?? $validated['name'];

        if ($slug !== $role?->slug) {
            $slug = Str::slug($slug);
        }

        $request->merge(['slug' => $slug]);
        $request->validate([
            'slug' => ['required', 'max:100', $uniqueSlug, Rule::notIn(Role::SYSTEM_SLUGS)],
            'name' => [
                $uniqueName,
                function (string $attribute, string $value, \Closure $fail): void {
                    if (in_array(Str::slug($value), Role::SYSTEM_SLUGS, true)) {
                        $fail('Este nombre está reservado.');
                    }
                },
            ],
        ], [
            'slug.required' => 'Escribe un identificador válido para este tipo.',
            'slug.unique' => 'Ya existe un tipo de participante con este identificador.',
            'slug.not_in' => 'Este identificador está reservado.',
            'name.unique' => 'Ya existe un tipo de participante con este nombre.',
        ]);

        $validated['slug'] = $slug;

        return $validated;
    }

    private function copyLegacyAccess(Role $role, Role $previousRole, bool $inherit = true): void
    {
        $previousRole->loadMissing('permissions');
        $legacyKeys = array_map($previousRole->legacyAccessKey(...), array_keys(Permission::RESOURCES));
        $legacyPermissions = Permission::query()->whereIn('resource', $legacyKeys)->get()->keyBy('resource');

        foreach (array_keys(Permission::RESOURCES) as $resource) {
            if (! $inherit && ! $legacyPermissions->has($previousRole->legacyAccessKey($resource))) {
                continue;
            }

            $flags = [];
            foreach (array_keys(Permission::ACTIONS) as $action) {
                $flags['can_'.$action] = $inherit && $previousRole->accessPermission($legacyPermissions, $resource, $action);
            }

            Permission::query()->firstOrCreate(['resource' => $role->accessKey($resource)], $flags);
        }
    }
}
