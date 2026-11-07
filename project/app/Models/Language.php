<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'iso_code', 'native_name'])]
class Language extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;
}
