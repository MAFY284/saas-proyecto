<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Administrador', 'slug' => 'administrador', 'description' => 'Administra la plataforma.'],
            ['name' => 'Organizador', 'slug' => 'organizador', 'description' => 'Gestiona una liga.'],
            ['name' => 'Entrenador', 'slug' => 'entrenador', 'description' => 'Administra un equipo.'],
            ['name' => 'Jugador', 'slug' => 'jugador', 'description' => 'Participa en una liga.'],
            ['name' => 'Árbitro', 'slug' => 'arbitro', 'description' => 'Oficia los partidos.'],
        ];

        foreach ($types as $type) {
            UserType::query()->updateOrCreate(
                ['slug' => $type['slug']],
                $type,
            );
        }
    }
}
