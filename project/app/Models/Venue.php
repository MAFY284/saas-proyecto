<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'locality_id',
    'address_id',
    'name',
    'address',
    'surface_type',
    'capacity',
    'operating_hours',
    'status',
])]
class Venue extends Model
{
    use HasFactory;

    public const STATUSES = [
        'available' => 'Disponible',
        'maintenance' => 'En mantenimiento',
        'inactive' => 'Inactiva',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }
}
