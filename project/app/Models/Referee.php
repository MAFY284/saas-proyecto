<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'locality_id',
    'first_name',
    'last_name',
    'middle_name',
    'birth_date',
    'phone',
    'email',
    'license_number',
    'years_experience',
    'category',
    'is_active',
])]
class Referee extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'years_experience' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(MatchReport::class);
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }
}
