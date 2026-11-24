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
    'local_score',
    'visitor_score',
    'observations',
    'referee1',
    'referee2',
    'referee3',
    'referee4',
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
            'local_score' => 'integer',
            'visitor_score' => 'integer',
            'referee1' => 'integer',
            'referee2' => 'integer',
            'referee3' => 'integer',
            'referee4' => 'integer',
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

    public function mainReferee(): BelongsTo
    {
        return $this->belongsTo(Referee::class, 'referee1');
    }

    public function secondReferee(): BelongsTo
    {
        return $this->belongsTo(Referee::class, 'referee2');
    }

    public function thirdReferee(): BelongsTo
    {
        return $this->belongsTo(Referee::class, 'referee3');
    }

    public function fourthReferee(): BelongsTo
    {
        return $this->belongsTo(Referee::class, 'referee4');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RefereeCardPlayer::class);
    }

    public function scoreLabel(): string
    {
        if ($this->local_score === null || $this->visitor_score === null) {
            return '—';
        }

        return $this->local_score.' - '.$this->visitor_score;
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
