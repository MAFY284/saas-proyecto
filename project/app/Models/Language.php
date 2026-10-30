<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'iso_code', 'native_name'])]
class Language extends Model
{
    public const UPDATED_AT = null;
}
