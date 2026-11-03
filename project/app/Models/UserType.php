<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description'])]
class UserType extends Model
{
    public const UPDATED_AT = null;

    /** @use HasFactory<\Database\Factories\UserTypeFactory> */
    use HasFactory;
}
