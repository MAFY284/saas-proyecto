<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'language_id',
    'name',
    'first_name',
    'last_name',
    'email',
    'password',
    'phone',
    'time_zone',
    'is_active',
    'avatar',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const BOOTSTRAP_ADMIN_EMAIL = 'admin@admin.com';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];

        $letters = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)));

        return $letters->implode('') ?: '?';
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    public function updateAvatar(UploadedFile $file): void
    {
        $this->deleteAvatarFile();
        $this->avatar = $file->store('avatars', 'public');
    }

    public function deleteAvatarFile(): void
    {
        if ($this->avatar) {
            Storage::disk('public')->delete($this->avatar);
        }
    }

    public function roles(): BelongsToMany
    {
        try {
            if (Schema::hasColumn('user_roles', 'user_id')) {
                return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
            }
        } catch (\Throwable $e) {
        }

        return $this->belongsToMany(Role::class, 'user_roles', 'league_user_id', 'role_id');
    }

    public function getRoles(): Collection
    {
        try {
            // 1. Si la tabla user_roles tiene 'league_user_id' y existe 'league_users'
            if (Schema::hasColumn('user_roles', 'league_user_id') && Schema::hasTable('league_users')) {
                $roleIds = DB::table('user_roles')
                    ->join('league_users', 'league_users.id', '=', 'user_roles.league_user_id')
                    ->where('league_users.user_id', $this->id)
                    ->pluck('user_roles.role_id');

                if ($roleIds->isNotEmpty()) {
                    return Role::whereIn('id', $roleIds)->get();
                }
            }

            // 2. Si la tabla user_roles tiene 'user_id'
            if (Schema::hasColumn('user_roles', 'user_id')) {
                $roleIds = DB::table('user_roles')
                    ->where('user_id', $this->id)
                    ->pluck('role_id');

                if ($roleIds->isNotEmpty()) {
                    return Role::whereIn('id', $roleIds)->get();
                }
            }

            // 3. Si users tiene 'role_id'
            if (Schema::hasColumn('users', 'role_id') && ! empty($this->role_id)) {
                $role = Role::find($this->role_id);
                return new Collection($role ? [$role] : []);
            }
        } catch (\Throwable $e) {
            // Ignorar errores
        }

        return new Collection;
    }

    public function role(): ?Role
    {
        try {
            $roles = $this->getRoles();

            return $roles->firstWhere('slug', Role::ADMIN_SLUG)
                ?? $roles->first(fn (Role $role) => ! $role->isParticipantType())
                ?? $roles->first();
        } catch (\Throwable $e) {
            // Ignorar errores
        }

        return null;
    }

    public function isAdmin(?Collection $roles = null): bool
    {
        return (bool) ($this->attributes['is_admin'] ?? false)
            || $this->isBootstrapAdmin()
            || ($roles ?? $this->getRoles())->contains(fn (Role $role) => $role->is_active && $role->slug === Role::ADMIN_SLUG);
    }

    public function isBootstrapAdmin(): bool
    {
        return (int) $this->getKey() === 1
            && strcasecmp((string) $this->email, self::BOOTSTRAP_ADMIN_EMAIL) === 0;
    }

    public function canManageAccount(User $target): bool
    {
        return ! $target->isAdmin() || $this->isAdmin();
    }

    public function canAccess(string $resource, bool $write = false): bool
    {
        return $this->canAccessAction($resource, $write ? 'write' : 'read');
    }

    public function canAccessAction(string $resource, string $action): bool
    {
        if (! $this->is_active || ! isset(Permission::ACTIONS[$action])) {
            return false;
        }

        $roles = $this->getRoles();

        if ($this->isAdmin($roles)) {
            return true;
        }

        if ($resource === 'roles' && $action !== 'read') {
            return false;
        }

        $participantRoles = $this->participantRoles($roles)->where('is_active', true)->load('permissions');

        if ($participantRoles->isEmpty()) {
            return false;
        }

        $accessKeys = $participantRoles->flatMap(fn (Role $role) => [
            $role->accessKey($resource),
            $role->legacyAccessKey($resource),
        ]);
        $permissions = Permission::query()->whereIn('resource', $accessKeys)->get()->keyBy('resource');

        return $participantRoles->contains(fn (Role $role) => $role->accessPermission($permissions, $resource, $action));
    }

    public function participantRoles(?Collection $roles = null): Collection
    {
        $participantRoles = ($roles ?? $this->getRoles())->filter(fn (Role $role) => $role->isParticipantType());

        if ($participantRoles->isNotEmpty()) {
            return $participantRoles;
        }

        $legacyType = $this->participant_type;

        if (! $legacyType && ! empty($this->user_type_id) && Schema::hasTable('user_types')) {
            $legacyType = DB::table('user_types')->where('id', $this->user_type_id)->value('slug');
        }

        if ($legacyType) {
            $slug = Str::slug($legacyType);

            return Role::query()->participantTypes()
                ->whereIn('slug', [$slug, 'participant:'.$slug, 'participant_'.$slug])
                ->get();
        }

        return $participantRoles;
    }

    public function participantTypesLabel(): string
    {
        return $this->participantRoles()->pluck('name')->implode(', ') ?: '—';
    }
}
