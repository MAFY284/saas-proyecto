<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'league_id',
    'name',
    'season',
    'start_date',
    'end_date',
    'format',
    'status',
])]
class Tournament extends Model
{
    use HasFactory;

    public const STATUSES = [
        'active' => 'Activo',
        'finished' => 'Finalizado',
        'cancelled' => 'Cancelado',
        'draft' => 'Borrador',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
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
