<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        if ($resource && $this->permissions->where('resource', $resource)->isNotEmpty()) {
            return $this->permissions->where('resource', $resource)->contains(fn ($p) => (bool) $p->can_read);
        }

        return $this->permissions->contains(fn ($p) => (bool) $p->can_read);
    }

    public function hasWritePermission(?string $resource = null): bool
    {
        if ($resource && $this->permissions->where('resource', $resource)->isNotEmpty()) {
            return $this->permissions->where('resource', $resource)->contains(fn ($p) => (bool) $p->can_write);
        }

        return $this->permissions->contains(fn ($p) => (bool) $p->can_write);
    }
}

