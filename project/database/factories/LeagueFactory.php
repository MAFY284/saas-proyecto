<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Currency;
use App\Models\League;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<League>
 */
class LeagueFactory extends Factory
{
    protected $model = League::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company().' League';

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->randomNumber(4)),
            'short_name' => fake()->lexify('???'),
            'description' => fake()->sentence(),
            'country_id' => Country::query()->inRandomOrder()->value('id') ?? Country::create(['name' => 'México', 'iso2_code' => 'MX'])->id,
            'currency_id' => Currency::query()->inRandomOrder()->value('id') ?? Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$'])->id,
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'logo_url' => null,
            'website_url' => fake()->url(),
            'time_zone' => 'UTC',
            'is_active' => true,
        ];
    }
}
