<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->languageCode(),
            'iso_code' => fake()->unique()->languageCode(),
            'native_name' => null,
        ];
    }
}
