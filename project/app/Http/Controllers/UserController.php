<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use App\Models\League;
use App\Models\Role;
use App\Models\User;
use App\Support\EmailValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->get('q', '')), 0, 100);

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $dateLike = '%'.str_replace(['/', '.'], '-', addcslashes($search, '%_\\')).'%';

                $query->where(function ($query) use ($like, $dateLike) {
                    $query->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('id', 'like', $like)
                        ->orWhere('created_at', 'like', $like)
                        ->orWhere('created_at', 'like', $dateLike);
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function create()
    {
        return view('users.create', [
            'user'                => new User,
            'isAdmin'             => false,
            'userParticipantType' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        if (auth()->check() && ! auth()->user()->canWrite()) {
            return back()->with('error', 'No tienes permisos de escritura para crear usuarios.');
        }

        $data = $this->validatedData($request);

        $user = new User;
        $this->fillUser($user, $data);

        if ($request->hasFile('avatar')) {
            $user->updateAvatar($request->file('avatar'));
        }

        $user->save();

        // Asignar rol Administrador o Usuario y tipo de participante
        $isAdmin = $request->boolean('is_admin');
        $role = $this->ensureRoleWithPermissions($isAdmin);
        $participantType = $data['participant_type'] ?? null;
        if ($isAdmin && $participantType === 'jugador') {
            $participantType = null;
        }

        $this->syncUserRole($user, $role->id, $participantType);

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'user'                => $user,
            'isAdmin'             => $user->isAdmin(),
            'userParticipantType' => $user->participantType(),
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        if (auth()->check() && ! auth()->user()->canWrite()) {
            return back()->with('error', 'No tienes permisos de escritura para actualizar usuarios.');
        }

        $data = $this->validatedData($request, $user);

        $this->fillUser($user, $data);

        if ($request->hasFile('avatar')) {
            $user->updateAvatar($request->file('avatar'));
        }

        $user->save();

        // Asignar rol Administrador o Usuario y tipo de participante
        $isAdmin = $request->boolean('is_admin');
        $role = $this->ensureRoleWithPermissions($isAdmin);
        $participantType = $data['participant_type'] ?? null;
        if ($isAdmin && $participantType === 'jugador') {
            $participantType = null;
        }

        $this->syncUserRole($user, $role->id, $participantType);

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario actualizado.');
    }

    public function destroy(Request $request, ?User $user = null)
    {
        if (auth()->check() && ! auth()->user()->canWrite()) {
            return back()->with('error', 'No tienes permisos de escritura para eliminar usuarios.');
        }

        $ids = collect($request->input('ids', []))
            ->when($user?->id, fn ($ids, $id) => $ids->push($id))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecciona al menos un usuario.');
        }

        $users = User::query()
            ->whereIn('id', $ids)
            ->whereKeyNot($request->user()->id)
            ->get();

        foreach ($users as $deletedUser) {
            $deletedUser->deleteAvatarFile();
        }

        $deleted = User::query()
            ->whereIn('id', $users->modelKeys())
            ->delete();

        if ($deleted === 0) {
            return back()->with('error', 'No se eliminó ningún usuario. No puedes eliminar tu propia cuenta.');
        }

        return redirect()
            ->route('users.index')
            ->with('status', $deleted === 1 ? 'Usuario eliminado.' : "{$deleted} usuarios eliminados.");
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ], [
            'ids.required' => 'Selecciona al menos un usuario.',
            'ids.min' => 'Selecciona al menos un usuario.',
        ]);

        $count = count($data['ids']);

        return back()->with(
            'status',
            $count === 1
                ? 'Proceso ejecutado para 1 usuario.'
                : "Proceso ejecutado para {$count} usuarios."
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $participantTypes = [
            'jugador'    => 'Jugador',
            'arbitro'    => 'Árbitro',
            'entrenador' => 'Entrenador',
            'delegado'   => 'Delegado',
            'directivo'  => 'Directivo',
        ];

        // Si la tabla user_types existe Y tiene registros, los usamos en su lugar.
        if (Schema::hasTable('user_types')) {
            $dbTypes = DB::table('user_types')
                ->orderBy('name')
                ->pluck('name', 'slug')
                ->all();

            if (! empty($dbTypes)) {
                $participantTypes = $dbTypes;
            }
        }

        return [
            'languages'        => Language::query()->orderBy('name')->get(),
            'timeZones'        => timezone_identifiers_list(),
            'roles'            => Role::query()->where('is_active', true)->orderBy('name')->get(),
            'participantTypes' => $participantTypes,
        ];
    }

    /**
     * Mantiene los roles del sistema y el rol de tipo de participante para cada usuario.
     */
    private function syncUserRole(User $user, mixed $systemRoleId, ?string $participantType = null): void
    {
        $roleIds = [];
        if ($systemRoleId) {
            $roleIds[] = (int) $systemRoleId;
        }

        if (filled($participantType)) {
            $participantRole = $this->ensureParticipantRole($participantType);
            $roleIds[] = (int) $participantRole->id;
        }

        $roleIds = array_values(array_unique(array_filter($roleIds)));

        try {
            // 1. Si la tabla user_roles tiene 'user_id' directamente
            if (Schema::hasColumn('user_roles', 'user_id')) {
                DB::table('user_roles')->where('user_id', $user->id)->delete();

                $tenantId = Schema::hasColumn('user_roles', 'tenant_id')
                    ? (DB::table('tenants')->value('id') ?? 1)
                    : null;

                foreach ($roleIds as $rId) {
                    $insertData = [
                        'user_id'    => $user->id,
                        'role_id'    => $rId,
                        'created_at' => now(),
                    ];
                    if ($tenantId !== null) {
                        $insertData['tenant_id'] = $tenantId;
                    }
                    DB::table('user_roles')->insert($insertData);
                }
            }

            // 2. Si la tabla user_roles tiene 'league_user_id' y existe 'league_users'
            if (Schema::hasColumn('user_roles', 'league_user_id') && Schema::hasTable('league_users')) {
                $leagueUserId = DB::table('league_users')->where('user_id', $user->id)->value('id');

                if (! $leagueUserId) {
                    $leagueId = League::query()->value('id');
                    if (! $leagueId) {
                        $countryId = Country::query()->value('id') ?? Country::create(['name' => 'México', 'iso2_code' => 'MX'])->id;
                        $currencyId = Currency::query()->value('id') ?? Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$'])->id;
                        $defaultLeague = League::create([
                            'name'        => 'Liga General',
                            'slug'        => 'liga-general',
                            'country_id'  => $countryId,
                            'currency_id' => $currencyId,
                            'time_zone'   => 'UTC',
                            'is_active'   => true,
                        ]);
                        $leagueId = $defaultLeague->id;
                    }

                    $leagueUserId = DB::table('league_users')->insertGetId([
                        'league_id'  => $leagueId,
                        'user_id'    => $user->id,
                        'is_active'  => true,
                        'created_at' => now(),
                    ]);
                }

                $leagueUserIds = DB::table('league_users')->where('user_id', $user->id)->pluck('id');
                DB::table('user_roles')->whereIn('league_user_id', $leagueUserIds)->delete();

                foreach ($roleIds as $rId) {
                    DB::table('user_roles')->insert([
                        'league_user_id' => $leagueUserId,
                        'role_id'        => $rId,
                        'created_at'     => now(),
                    ]);
                }
            }

            // 3. Si la tabla users tiene columna role_id
            if (Schema::hasColumn('users', 'role_id') && $systemRoleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => (int) $systemRoleId]);
            }

            // 4. Si la tabla users tiene columna participant_type directamente
            if (Schema::hasColumn('users', 'participant_type')) {
                DB::table('users')->where('id', $user->id)->update(['participant_type' => $participantType]);
            }

            // 5. Si la tabla users tiene columna user_type_id y existe user_types
            if (Schema::hasColumn('users', 'user_type_id') && Schema::hasTable('user_types')) {
                $typeId = filled($participantType)
                    ? DB::table('user_types')->where('slug', $participantType)->value('id')
                    : null;
                DB::table('users')->where('id', $user->id)->update(['user_type_id' => $typeId]);
            }

            // Forzar recarga limpia de la relación en memoria
            $user->unsetRelation('roles');
        } catch (\Throwable $e) {
            // Ignorar errores silenciosamente
        }
    }

    /**
     * Asegura la existencia del rol de tipo de participante y vincula sus permisos.
     */
    private function ensureParticipantRole(string $participantType): Role
    {
        $types = [
            'jugador'    => 'Jugador',
            'arbitro'    => 'Árbitro',
            'entrenador' => 'Entrenador',
            'delegado'   => 'Delegado',
            'directivo'  => 'Directivo',
        ];

        $name = $types[$participantType] ?? ucfirst($participantType);
        $slug = 'participant:' . $participantType;

        $role = Role::query()
            ->where('slug', $slug)
            ->orWhere('slug', $participantType)
            ->orWhere('name', $name)
            ->first();

        if (! $role) {
            $leagueId = null;
            if (Schema::hasColumn('roles', 'league_id')) {
                $leagueId = League::query()->value('id');
                if (! $leagueId) {
                    $countryId = Country::query()->value('id') ?? Country::create(['name' => 'México', 'iso2_code' => 'MX'])->id;
                    $currencyId = Currency::query()->value('id') ?? Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$'])->id;
                    $defaultLeague = League::create([
                        'name'        => 'Liga General',
                        'slug'        => 'liga-general',
                        'country_id'  => $countryId,
                        'currency_id' => $currencyId,
                        'time_zone'   => 'UTC',
                        'is_active'   => true,
                    ]);
                    $leagueId = $defaultLeague->id;
                }
            }

            $role = new Role([
                'name'        => $name,
                'slug'        => $slug,
                'description' => 'Rol de participante: ' . $name,
                'is_active'   => true,
            ]);
            if ($leagueId) {
                $role->league_id = $leagueId;
            }
            $role->save();
        }

        // Si existen permisos en AccessController, vincularlos al rol
        $permissionIds = DB::table('permissions')
            ->where('resource', 'like', "access:{$participantType}:%")
            ->pluck('id')
            ->all();

        if (! empty($permissionIds)) {
            $role->permissions()->sync($permissionIds);
        }

        return $role;
    }

    private function userRoleId(User $user): ?int
    {
        $role = $user->role();
        if ($role) {
            return $role->id;
        }

        if (Schema::hasColumn('user_roles', 'user_id')) {
            return $user->roles()->value('roles.id');
        }

        if (Schema::hasColumn('user_roles', 'league_user_id') && Schema::hasTable('league_users')) {
            return DB::table('user_roles')
                ->join('league_users', 'league_users.id', '=', 'user_roles.league_user_id')
                ->where('league_users.user_id', $user->id)
                ->value('user_roles.role_id');
        }

        return null;
    }

    private function participantType(User $user): ?string
    {
        return $user->participantType();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function fillUser(User $user, array $data): void
    {
        $user->fill([
            'language_id' => $data['language_id'] ?? null,
            'name' => $data['name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'time_zone' => $data['time_zone'],
            'is_active' => $data['is_active'],
        ]);

        // Si es administrador, no puede ser 'jugador'
        $participantType = $data['participant_type'] ?? null;
        if (! empty($data['is_admin']) && $participantType === 'jugador') {
            $participantType = null;
        }

        // Si el esquema desplegado tiene estas columnas, se asignan
        if (Schema::hasColumn('users', 'participant_type')) {
            $user->participant_type = $participantType;
        }

        if (Schema::hasColumn('users', 'user_type_id') && Schema::hasTable('user_types')) {
            $user->user_type_id = filled($participantType)
                ? DB::table('user_types')->where('slug', $participantType)->value('id')
                : null;
        }

        if (Schema::hasColumn('users', 'is_admin')) {
            $user->is_admin = ! empty($data['is_admin']);
        }

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'language_id' => ['required', 'integer', 'exists:languages,id'],
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name' => ['required', 'string', 'min:2', 'max:50'],
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'email' => EmailValidation::rules($user),
            'phone' => ['nullable', 'string', 'max:50'],
            'time_zone' => ['required', 'string', 'max:100', Rule::in(timezone_identifiers_list())],
            'is_active' => ['required', 'boolean'],
            'is_admin' => ['nullable', 'boolean'],
            'participant_type' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->boolean('is_admin') && $value === 'jugador') {
                        $fail('El tipo "Jugador" no está disponible para administradores.');
                    }
                },
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ], array_merge(EmailValidation::messages(), [
            'language_id.required' => 'El idioma es obligatorio.',
            'language_id.exists' => 'El idioma no es válido.',
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'first_name.max' => 'El nombre no puede superar 50 caracteres.',
            'last_name.required' => 'El apellido es obligatorio.',
            'last_name.min' => 'El apellido debe tener al menos 2 caracteres.',
            'last_name.max' => 'El apellido no puede superar 50 caracteres.',
            'name.required' => 'El nombre visible es obligatorio.',
            'name.min' => 'El nombre visible debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre visible no puede superar 50 caracteres.',
            'phone.max' => 'El teléfono no puede superar 50 caracteres.',
            'time_zone.required' => 'La zona horaria es obligatoria.',
            'time_zone.in' => 'La zona horaria no es válida.',
            'password.required' => 'El password es obligatorio.',
            'password.min' => 'El password debe tener al menos 8 caracteres.',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.max' => 'La imagen no puede superar 2 MB.',
        ]));
    }

    /**
     * Asegura la existencia del rol y garantiza que tenga los permisos requeridos.
     */
    private function ensureRoleWithPermissions(bool $isAdmin): Role
    {
        $roleName = $isAdmin ? 'Administrador' : 'Usuario';
        $roleSlug = $isAdmin ? 'admin' : 'usuario';
        $description = $isAdmin ? 'Acceso total con todos los permisos del sistema.' : 'Usuario estándar con permisos de lectura.';

        // Buscar rol existente buscando coincidencias flexibles
        $role = Role::query()
            ->where(function ($query) use ($isAdmin) {
                if ($isAdmin) {
                    $query->where('slug', 'like', '%admin%')
                        ->orWhere('name', 'like', '%admin%');
                } else {
                    $query->where('slug', 'like', '%usuario%')
                        ->orWhere('name', 'like', '%usuario%');
                }
            })
            ->first();

        if (! $role) {
            $leagueId = null;
            if (Schema::hasColumn('roles', 'league_id')) {
                $leagueId = League::query()->value('id');
                if (! $leagueId) {
                    $countryId = Country::query()->value('id') ?? Country::create(['name' => 'México', 'iso2_code' => 'MX'])->id;
                    $currencyId = Currency::query()->value('id') ?? Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$'])->id;
                    $league = League::create([
                        'name'        => 'Liga General',
                        'slug'        => 'liga-general',
                        'country_id'  => $countryId,
                        'currency_id' => $currencyId,
                        'time_zone'   => 'UTC',
                        'is_active'   => true,
                    ]);
                    $leagueId = $league->id;
                }
            }

            $role = new Role([
                'name' => $roleName,
                'slug' => $roleSlug,
                'description' => $description,
                'is_active' => true,
            ]);
            if ($leagueId) {
                $role->league_id = $leagueId;
            }
            $role->save();
        }

        // Permisos: Administrador tiene lectura y escritura total; Usuario solo lectura
        $canRead = true;
        $canWrite = $isAdmin;

        $prefix = $role->slug . '_';
        $permissionIds = [];

        // Permiso sistema
        $systemPerm = \App\Models\Permission::updateOrCreate(
            ['resource' => $prefix . 'sistema'],
            [
                'can_read'   => $canRead ? 1 : 0,
                'can_write'  => $canWrite ? 1 : 0,
                'can_create' => $canWrite ? 1 : 0,
                'can_delete' => $canWrite ? 1 : 0,
            ]
        );
        $permissionIds[] = $systemPerm->id;

        // Permisos por recurso
        foreach (\App\Models\Permission::RESOURCES as $resourceKey => $resourceLabel) {
            $perm = \App\Models\Permission::updateOrCreate(
                ['resource' => $prefix . $resourceKey],
                [
                    'can_read'   => $canRead ? 1 : 0,
                    'can_write'  => $canWrite ? 1 : 0,
                    'can_create' => $canWrite ? 1 : 0,
                    'can_delete' => $canWrite ? 1 : 0,
                ]
            );
            $permissionIds[] = $perm->id;
        }

        $role->permissions()->sync(array_unique($permissionIds));

        return $role;
    }
}
