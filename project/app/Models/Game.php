<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'tournament_id',
    'home_team_id',
    'away_team_id',
    'venue_id',
    'referee_id',
    'match_date',
    'home_goals',
    'away_goals',
    'matchday',
    'status',
    'notes',
])]
class Game extends Model
{
    use HasFactory;

    public const STATUSES = [
        'scheduled' => 'Programado',
        'in_progress' => 'En juego',
        'finished' => 'Finalizado',
        'cancelled' => 'Cancelado',
        'postponed' => 'Pospuesto',
    ];

    protected $table = 'matches';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'home_goals' => 'integer',
            'away_goals' => 'integer',
            'matchday' => 'integer',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(Referee::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(MatchReport::class, 'match_id');
    }

    public function scoreLabel(): string
    {
        if ($this->home_goals === null || $this->away_goals === null) {
            return '—';
        }

        return $this->home_goals.' - '.$this->away_goals;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            'scheduled' => 'bg-slate-100 text-slate-700',
            'in_progress' => 'bg-amber-100 text-amber-800',
            'finished' => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-red-100 text-red-700',
            'postponed' => 'bg-violet-100 text-violet-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
