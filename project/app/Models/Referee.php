<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Referee extends Player
{
    protected $table = 'players';

    protected static function booted(): void
    {
        static::addGlobalScope('referee', function (Builder $builder) {
            $builder->where('type_id', 'referee');
        });
    }
}
