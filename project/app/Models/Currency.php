<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'symbol'])]
class Currency extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return HasMany<League, $this>
     */
    public function leagues(): HasMany
    {
        return $this->hasMany(League::class);
    }
}

