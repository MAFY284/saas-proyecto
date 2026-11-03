<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'team_id',
    'first_name',
    'last_name',
    'jersey_number',
    'position',
    'birth_date',
    'photo_url',
    'status',
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
            'jersey_number' => 'integer',
            'birth_date' => 'date',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(MatchReportPlayer::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
