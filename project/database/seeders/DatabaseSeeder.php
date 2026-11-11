<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Language;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Idiomas Base (Inglés y Español)
        $idiomas = [
            ['name' => 'Español', 'iso_code' => 'es', 'native_name' => 'Español'],
            ['name' => 'English', 'iso_code' => 'en', 'native_name' => 'English'],
        ];

        foreach ($idiomas as $idioma) {
            Language::query()->firstOrCreate(
                ['iso_code' => $idioma['iso_code']],
                ['name' => $idioma['name'], 'native_name' => $idioma['native_name']]
            );
        }

        $spanishLanguage = Language::where('iso_code', 'es')->first();

        // 2. Crear Usuario Principal Administrador
        User::query()->firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'language_id' => $spanishLanguage->id,
                'name' => 'Admin Sistema',
                'first_name' => 'Admin',
                'last_name' => 'Sistema',
                'password' => bcrypt('password'), // La contraseña será: password
                'is_active' => 1,
            ]
        );

        // 3. Crear 10 Usuarios Ficticios usando tu UserFactory
        User::factory(10)->create([
            'language_id' => $spanishLanguage->id,
            'password' => bcrypt('password'),
        ]);

        // 4. Llamar al Seeder de Fútbol sin alterarlo
        $this->call([
            FootballDemoSeeder::class,
        ]);
    }
}