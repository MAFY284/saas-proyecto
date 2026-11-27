<?php

use Database\Seeders\DemoAdminSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:demo', function (DemoAdminSeeder $seeder): int {
    if (! app()->environment('local', 'testing')) {
        $this->error('El administrador de pruebas solo se puede preparar en local o testing.');

        return 1;
    }

    $user = $seeder->provision();

    if (! $user->is_active || ! $user->isAdmin()) {
        $this->error('El correo de pruebas ya existe, pero la cuenta está inactiva o no es administradora.');

        return 1;
    }

    $this->info($user->wasRecentlyCreated
        ? 'Administrador de pruebas creado.'
        : 'El administrador de pruebas ya existe. Se conservó su contraseña.');

    return 0;
})->purpose('Preparar el administrador de pruebas de una instalación local');
