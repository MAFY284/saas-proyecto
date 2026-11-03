<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_las_iniciales_salen_del_nombre_visible(): void
    {
        $user = new User(['name' => 'Ana Ruiz']);

        $this->assertSame('AR', $user->initials());
    }

    public function test_un_solo_nombre_deja_una_inicial(): void
    {
        $user = new User(['name' => '  Ana  ']);

        $this->assertSame('A', $user->initials());
    }

    public function test_sin_nombre_la_inicial_es_un_signo_de_interrogacion(): void
    {
        $user = new User(['name' => '   ']);

        $this->assertSame('?', $user->initials());
    }

    public function test_la_contrasena_se_guarda_hasheada(): void
    {
        $user = new User;
        $user->password = 'secret-password';

        $this->assertNotSame('secret-password', $user->password);
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }

    public function test_is_active_se_lee_como_booleano(): void
    {
        $user = new User;

        $user->is_active = 1;
        $this->assertTrue($user->is_active);

        $user->is_active = 0;
        $this->assertFalse($user->is_active);
    }

    public function test_sin_avatar_no_hay_url(): void
    {
        Storage::fake('public');

        $this->assertNull((new User)->avatarUrl());
    }

    public function test_actualizar_el_avatar_reemplaza_el_archivo_anterior(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.png', 'anterior');

        $user = new User(['avatar' => 'avatars/old.png']);
        $user->updateAvatar(UploadedFile::fake()->image('foto.jpg'));

        Storage::disk('public')->assertMissing('avatars/old.png');
        Storage::disk('public')->assertExists($user->avatar);
        $this->assertNotNull($user->avatarUrl());
    }
}
