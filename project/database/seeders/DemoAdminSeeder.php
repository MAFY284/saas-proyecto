<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing') || User::query()->exists()) {
            return;
        }

        $this->provision();
    }

    public function provision(): User
    {
        return DB::transaction(function () {
            $email = User::BOOTSTRAP_ADMIN_EMAIL;
            $existing = User::query()->where('email', $email)->first();

            if ($existing) {
                return $existing;
            }

            $language = Language::query()->firstOrCreate(
                ['iso_code' => 'es'],
                ['name' => 'Español', 'native_name' => 'Español'],
            );

            $user = User::query()->firstOrCreate(['email' => $email], [
                'language_id' => $language->id,
                'name' => 'Admin Sistema',
                'first_name' => 'Admin',
                'last_name' => 'Sistema',
                'password' => 'password',
                'is_active' => true,
            ]);

            if ($user->wasRecentlyCreated && ! $user->isBootstrapAdmin()) {
                $role = Role::query()->firstOrCreate(
                    ['slug' => Role::ADMIN_SLUG, 'is_active' => true],
                    ['name' => 'Administrador', 'description' => 'Acceso total al sistema.'],
                );

                $user->roles()->attach($role->id, ['created_at' => now()]);
            }

            return $user;
        });
    }
}
