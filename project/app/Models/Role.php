<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable([
    'league_id',
    'name',
    'slug',
    'description',
    'is_active',
])]
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    public const ADMIN_SLUG = 'admin';

    public const SYSTEM_SLUGS = ['admin', 'administrador', 'administrator', 'usuario', 'user'];

    public function scopeParticipantTypes(Builder $query): Builder
    {
        return $query->whereNotIn(DB::raw('LOWER(slug)'), self::SYSTEM_SLUGS);
    }

    public function isParticipantType(): bool
    {
        return ! in_array(strtolower($this->slug), self::SYSTEM_SLUGS, true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
            ->withPivot('created_at');
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class, 'league_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    public function hasReadPermission(?string $resource = null): bool
    {
        return $this->hasPermission('can_read', $resource);
    }

    public function hasWritePermission(?string $resource = null): bool
    {
        return $this->hasPermission('can_write', $resource);
    }

    private function hasPermission(string $column, ?string $resource): bool
    {
        if ($resource === null) {
            return $this->permissions->contains($column, true);
        }

        $permissions = $this->permissions->whereIn('resource', [$resource, "{$this->slug}_{$resource}"]);

        if ($permissions->isEmpty() && $this->permissions->count() === 1) {
            $permissions = $this->permissions->where('resource', "{$this->slug}_sistema");
        }

        return $permissions->contains($column, true);
    }

    public function participantType(): string
    {
        $slug = strtolower($this->slug);

        if (str_starts_with($slug, 'participant:') || str_starts_with($slug, 'participant_')) {
            return substr($slug, strlen('participant:'));
        }

        $legacyTypes = ['jugador', 'arbitro', 'entrenador', 'delegado', 'directivo'];
        $name = Str::slug($this->name);

        if (! in_array($slug, $legacyTypes, true) && in_array($name, $legacyTypes, true)) {
            return $name;
        }

        return $slug;
    }

    public function accessKey(string $resource): string
    {
        return "access:role-{$this->id}:{$resource}";
    }

    public function legacyAccessKey(string $resource): string
    {
        return "access:{$this->participantType()}:{$resource}";
    }

    public function accessPermission(Collection $permissions, string $resource, string $action = 'read'): bool
    {
        $permission = $permissions[$this->accessKey($resource)]
            ?? $permissions[$this->legacyAccessKey($resource)]
            ?? null;

        $column = 'can_'.$action;

        return $permission === null ? $this->hasPermission($column, $resource) : $permission->{$column};
    }
}

