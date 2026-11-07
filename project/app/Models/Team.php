<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'locality_id',
    'name',
    'slug',
    'short_name',
    'logo_url',
    'contact_email',
    'contact_phone',
    'is_active',
])]
class Team extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'team_players')
            ->withPivot(['captain', 'jersey_number'])
            ->withTimestamps();
    }

    public function localGames(): HasMany
    {
        return $this->hasMany(Game::class, 'local_team_id');
    }

    public function visitorGames(): HasMany
    {
        return $this->hasMany(Game::class, 'visitor_team_id');
    }
}
