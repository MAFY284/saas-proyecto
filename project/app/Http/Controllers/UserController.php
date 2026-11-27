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
use Illuminate\Database\Eloquent\Collection;
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
            'user' => new User,
            'isAdmin' => false,
            'selectedParticipantIds' => [],
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $this->saveUser($request, new User, $data);

        return redirect()
            ->route($request->user()->canAccess('users') ? 'users.index' : 'home')
            ->with('status', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        $this->authorizeUserEdit(request(), $user);

        return view('users.edit', [
            'user' => $user,
            'isAdmin' => $user->isAdmin(),
            'selectedParticipantIds' => $user->participantRoles()->modelKeys(),
            ...$this->formOptions($user),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUserEdit($request, $user);
        $data = $this->validatedData($request, $user);
        $this->saveUser($request, $user, $data);

        return redirect()
            ->route($request->user()->canAccess('users') ? 'users.index' : 'home')
            ->with('status', 'Usuario actualizado.');
    }

    public function destroy(Request $request, ?User $user = null)
    {
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

        abort_if($users->contains(fn (User $target) => $target->isBootstrapAdmin()
            || ! $request->user()->canManageAccount($target)), 403);

        foreach ($users as $deletedUser) {
            $deletedUser->deleteAvatarFile();
        }

        $userIds = $users->modelKeys();

        // Eliminar roles asociados antes de borrar el usuario (FK restrictOnDelete)
        DB::table('user_roles')->whereIn('user_id', $userIds)->delete();

        $deleted = User::query()
            ->whereIn('id', $userIds)
            ->delete();

        if ($deleted === 0) {
            return back()->with('error', 'No se eliminó ningún usuario. No puedes eliminar tu propia cuenta.');
        }

        return redirect()
            ->route($request->user()->canAccess('users') ? 'users.index' : 'home')
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
    private function formOptions(?User $user = null): array
    {
        return [
            'languages' => Language::query()->orderBy('name')->get(),
            'timeZones' => timezone_identifiers_list(),
            'participantTypes' => $this->availableParticipantTypes($user),
        ];
    }

    private function availableParticipantTypes(?User $user = null): Collection
    {
        $assignedIds = $user?->participantRoles()->modelKeys() ?? [];

        return Role::query()->participantTypes()
            ->where(fn ($query) => $query->where('is_active', true)->orWhereIn('id', $assignedIds))
            ->orderBy('name')
            ->get();
    }

    private function authorizeUserEdit(Request $request, User $user): void
    {
        abort_unless($request->user()->canManageAccount($user), 403);
    }

    private function saveUser(Request $request, User $user, array $data): void
    {
        DB::transaction(function () use ($request, $user, $data) {
            $this->fillUser($user, $data);

            if ($request->hasFile('avatar')) {
                $user->updateAvatar($request->file('avatar'));
            }

            $user->save();

            if (! $request->user()->isAdmin()) {
                return;
            }

            $participantIds = $data['is_admin']
                ? []
                : array_values($data['participant_role_ids'] ?? $user->participantRoles()->modelKeys());
            $roleIds = $participantIds;

            if ($data['is_admin']) {
                array_unshift($roleIds, $this->ensureAdminRole()->id);
            }

            $this->syncUserRole($user, $roleIds, $participantIds, $data['is_admin']);
        });
    }

    /**
     * Mantiene los roles del sistema y el rol de tipo de participante para cada usuario.
     */
    private function syncUserRole(User $user, array $roleIds, array $participantIds, bool $isAdmin): void
    {
        $systemRoleId = $roleIds[0] ?? null;
        $participantType = isset($participantIds[0])
            && (Schema::hasColumn('users', 'participant_type') || Schema::hasColumn('users', 'user_type_id'))
            ? Role::find($participantIds[0])?->participantType()
            : null;

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

            if (! $leagueUserId && $roleIds !== []) {
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
        if (Schema::hasColumn('users', 'role_id')) {
            DB::table('users')->where('id', $user->id)->update(['role_id' => $systemRoleId]);
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

        if (Schema::hasColumn('users', 'is_admin')) {
            DB::table('users')->where('id', $user->id)->update(['is_admin' => $isAdmin]);
        }

        // Forzar recarga limpia de la relación en memoria
        $user->unsetRelation('roles');
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
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?User $user = null): array
    {
        $canManageRoles = $request->user()->isAdmin();
        abort_if(! $canManageRoles && ($request->boolean('is_admin')
            || $request->hasAny(['participant_types_present', 'participant_role_ids'])), 403);

        $managesOtherUser = $user && ! $user->is($request->user()) && ! $canManageRoles;
        $data = $request->validate([
            'language_id' => ['required', 'integer', 'exists:languages,id'],
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name' => ['required', 'string', 'min:2', 'max:50'],
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'email' => [
                ...EmailValidation::rules($user),
                function (string $attribute, string $value, \Closure $fail) use ($user, $managesOtherUser): void {
                    if (strcasecmp($value, User::BOOTSTRAP_ADMIN_EMAIL) === 0 && ! $user?->isBootstrapAdmin()) {
                        $fail('El email está reservado.');
                    }

                    if (($managesOtherUser || $user?->isBootstrapAdmin()) && $value !== $user->email) {
                        $fail('No tienes autorización para cambiar este email.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'time_zone' => ['required', 'string', 'max:100', Rule::in(timezone_identifiers_list())],
            'is_active' => ['required', 'boolean'],
            'is_admin' => ['nullable', 'boolean'],
            'participant_types_present' => ['sometimes', 'accepted'],
            'participant_role_ids' => ['sometimes', 'array', 'list'],
            'participant_role_ids.*' => [
                'required', 'integer', 'distinct',
                Rule::in($canManageRoles ? $this->availableParticipantTypes($user)->modelKeys() : []),
            ],
            'password' => [$user ? 'nullable' : 'required', Rule::prohibitedIf($managesOtherUser), 'string', 'min:8'],
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
            'participant_role_ids.*.in' => 'Selecciona un tipo de participante disponible.',
            'password.required' => 'El password es obligatorio.',
            'password.min' => 'El password debe tener al menos 8 caracteres.',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.max' => 'La imagen no puede superar 2 MB.',
        ]));

        if ($canManageRoles) {
            $data['is_admin'] = $user?->isBootstrapAdmin()
                || (bool) ($data['is_admin'] ?? $user?->isAdmin());

            if ($user?->isBootstrapAdmin()) {
                $data['is_active'] = true;
            }

            if (isset($data['participant_types_present'])) {
                $data['participant_role_ids'] ??= [];
            }
        }

        return $data;
    }

    /**
     * Obtiene el rol interno que respalda la casilla de administrador.
     */
    private function ensureAdminRole(): Role
    {
        $role = Role::query()->where('slug', Role::ADMIN_SLUG)->where('is_active', true)->first();

        if ($role) {
            return $role;
        }

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
                'name' => 'Administrador',
                'slug' => Role::ADMIN_SLUG,
                'description' => 'Acceso total con todos los permisos del sistema.',
                'is_active' => true,
            ]);
            if ($leagueId) {
                $role->league_id = $leagueId;
            }
            $role->save();
        }

        return $role;
    }
}
