<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'resource',
    'can_read',
    'can_write',
    'can_create',
    'can_delete',
])]
class Permission extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    public const RESOURCES = [
        'users' => 'Usuarios',
        'leagues' => 'Ligas',
        'teams' => 'Equipos',
        'matches' => 'Partidos',
        'match_reports' => 'Actas de partido',
        'tournaments' => 'Torneos',
        'fields' => 'Canchas',
        'roles' => 'Roles y Permisos',
    ];

    public const RESOURCE_TABLES = [
        'users' => 'users',
        'leagues' => 'leagues',
        'teams' => 'teams',
        'matches' => 'matches',
        'match_reports' => 'match_sheets',
        'tournaments' => 'tournaments',
        'fields' => 'fields',
        'roles' => 'roles',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'can_read' => 'boolean',
            'can_write' => 'boolean',
            'can_create' => 'boolean',
            'can_delete' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }
}

