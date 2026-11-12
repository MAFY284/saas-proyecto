<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'match_id',
    'field_id',
    'city',
    'match_date',
    'observations',
])]
class RefereeCard extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'match_date' => 'date',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'match_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RefereeCardPlayer::class);
    }

    public function matchLabel(): string
    {
        $local = $this->game?->localTeam?->name;
        $visitor = $this->game?->visitorTeam?->name;

        if (! $local && ! $visitor) {
            return '—';
        }

        return ($local ?? 'Local').' vs '.($visitor ?? 'Visitante');
    }
}
