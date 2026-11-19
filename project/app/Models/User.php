<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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

    public function getRoles()
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
                return $role ? collect([$role]) : collect();
            }
        } catch (\Throwable $e) {
            // Ignorar errores
        }

        return collect();
    }

    public function role(): ?Role
    {
        try {
            $roles = $this->getRoles();

            // Prioridad 1: Rol de Administrador
            $adminRole = $roles->first(function ($r) {
                $slug = strtolower((string) $r->slug);
                $name = strtolower((string) $r->name);
                return str_contains($slug, 'admin') || str_contains($name, 'admin');
            });

            if ($adminRole) {
                return $adminRole;
            }

            // Prioridad 2: Rol estándar de Usuario
            $userRole = $roles->first(function ($r) {
                $slug = strtolower((string) $r->slug);
                $name = strtolower((string) $r->name);
                return str_contains($slug, 'usuario') || str_contains($name, 'usuario');
            });

            if ($userRole) {
                return $userRole;
            }

            // Prioridad 3: Cualquier rol que no sea de participante
            $nonParticipantRole = $roles->first(function ($r) {
                return ! str_starts_with(strtolower((string) $r->slug), 'participant');
            });

            if ($nonParticipantRole) {
                return $nonParticipantRole;
            }

            return $roles->first();
        } catch (\Throwable $e) {
            // Ignorar errores
        }

        return null;
    }

    public function isAdmin(): bool
    {
        if (isset($this->attributes['is_admin']) && $this->attributes['is_admin']) {
            return true;
        }

        try {
            $roles = $this->getRoles();
            foreach ($roles as $role) {
                $slug = strtolower((string) $role->slug);
                $name = strtolower((string) $role->name);
                if (str_contains($slug, 'admin') || str_contains($name, 'admin')) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // Ignorar errores
        }

        $role = $this->role();
        if ($role) {
            $slug = strtolower((string) $role->slug);
            $name = strtolower((string) $role->name);
            if (str_contains($slug, 'admin') || str_contains($name, 'admin')) {
                return true;
            }
        }

        if ($this->id === 1) {
            return true;
        }

        return false;
    }

    public function canWrite(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        try {
            $roles = $this->getRoles();
            foreach ($roles as $role) {
                if ($role->hasWritePermission()) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
        }

        $role = $this->role();
        return $role ? $role->hasWritePermission() : false;
    }

    public function canRead(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        try {
            $roles = $this->getRoles();
            foreach ($roles as $role) {
                if ($role->hasReadPermission()) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
        }

        $role = $this->role();
        return $role ? $role->hasReadPermission() : true;
    }

    public function participantType(): ?string
    {
        try {
            // 1. Si la tabla users tiene columna participant_type directamente
            if (Schema::hasColumn('users', 'participant_type') && ! empty($this->participant_type)) {
                return $this->participant_type;
            }

            // 2. Si la tabla users tiene user_type_id y existe user_types
            if (Schema::hasColumn('users', 'user_type_id') && ! empty($this->user_type_id) && Schema::hasTable('user_types')) {
                $slug = DB::table('user_types')->where('id', $this->user_type_id)->value('slug');
                if ($slug) {
                    return $slug;
                }
            }

            // 3. Buscar en roles asociados (user_roles) usando getRoles() compatible
            $knownSlugs = ['jugador', 'arbitro', 'entrenador', 'delegado', 'directivo'];
            $knownNames = [
                'jugador'    => 'jugador',
                'árbitro'    => 'arbitro',
                'arbitro'    => 'arbitro',
                'entrenador' => 'entrenador',
                'delegado'   => 'delegado',
                'directivo'  => 'directivo',
            ];

            $roles = $this->getRoles();

            foreach ($roles as $role) {
                $slug = strtolower((string) $role->slug);
                if (str_starts_with($slug, 'participant:')) {
                    return substr($slug, strlen('participant:'));
                }
                if (str_starts_with($slug, 'participant_')) {
                    return substr($slug, strlen('participant_'));
                }
                if (in_array($slug, $knownSlugs, true)) {
                    return $slug;
                }
                $name = strtolower((string) $role->name);
                if (isset($knownNames[$name])) {
                    return $knownNames[$name];
                }
            }
        } catch (\Throwable $e) {
            // Ignorar errores
        }

        return null;
    }

    public function participantTypeLabel(): string
    {
        $types = [
            'jugador'    => 'Jugador',
            'arbitro'    => 'Árbitro',
            'entrenador' => 'Entrenador',
            'delegado'   => 'Delegado',
            'directivo'  => 'Directivo',
        ];

        $type = $this->participantType();
        return $types[$type] ?? ($type ? ucfirst($type) : '—');
    }
}
