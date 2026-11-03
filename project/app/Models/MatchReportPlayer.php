<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'match_report_id',
    'player_id',
    'team_id',
    'is_starter',
    'goals',
    'yellow_cards',
    'red_cards',
])]
class MatchReportPlayer extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_starter' => 'boolean',
            'goals' => 'integer',
            'yellow_cards' => 'integer',
            'red_cards' => 'integer',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(MatchReport::class, 'match_report_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
