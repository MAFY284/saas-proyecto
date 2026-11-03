<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'match_id',
    'referee_id',
    'venue_id',
    'match_date',
    'home_score',
    'away_score',
    'incidents',
    'status',
])]
class MatchReport extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Borrador',
        'completed' => 'Finalizada',
        'cancelled' => 'Cancelada',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'home_score' => 'integer',
            'away_score' => 'integer',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'match_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(Referee::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(MatchReportPlayer::class);
    }

    public function scoreLabel(): string
    {
        if ($this->home_score === null || $this->away_score === null) {
            return '—';
        }

        return $this->home_score.' - '.$this->away_score;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            'draft' => 'bg-slate-100 text-slate-700',
            'completed' => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
