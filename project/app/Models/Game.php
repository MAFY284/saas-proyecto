<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'tournament_id',
    'local_team_id',
    'visitor_team_id',
    'field_id',
    'date_time',
    'local_goals',
    'visitor_goals',
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
            'date_time' => 'datetime',
            'local_goals' => 'integer',
            'visitor_goals' => 'integer',
            'matchday' => 'integer',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function localTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'local_team_id');
    }

    public function visitorTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'visitor_team_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(MatchReport::class, 'match_id');
    }

    public function refereeCard(): HasOne
    {
        return $this->hasOne(RefereeCard::class, 'match_id');
    }

    public function scoreLabel(): string
    {
        if (in_array($this->status, ['scheduled', 'postponed', 'cancelled'], true)) {
            return '—';
        }

        return $this->local_goals.' - '.$this->visitor_goals;
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
