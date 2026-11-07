<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'match_id',
    'match_date',
    'local_score',
    'visitor_score',
    'incidents',
    'status',
    'referee1',
    'referee2',
    'referee3',
    'referee4',
])]
class MatchReport extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Borrador',
        'completed' => 'Finalizada',
        'cancelled' => 'Cancelada',
    ];

    protected $table = 'match_sheets';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
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
        return $this->hasMany(MatchReportPlayer::class, 'match_sheet_id');
    }

    public function scoreLabel(): string
    {
        if ($this->local_score === null || $this->visitor_score === null) {
            return '—';
        }

        return $this->local_score.' - '.$this->visitor_score;
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
