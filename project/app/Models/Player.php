<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'person_id',
    'type_id',
    'photo_url',
    'goals',
])]
class Player extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'goals' => 'integer',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(PersonData::class, 'person_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_players')
            ->withPivot(['captain', 'jersey_number'])
            ->withTimestamps();
    }

    public function entries(): HasMany
    {
        return $this->hasMany(MatchReportPlayer::class);
    }

    public function fullName(): string
    {
        return $this->person?->fullName() ?? 'Jugador #'.$this->id;
    }
}
