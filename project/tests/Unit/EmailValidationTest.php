<?php

namespace Tests\Unit;

use App\Support\EmailValidation;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EmailValidationTest extends TestCase
{
    public function test_acepta_formatos_validos_con_extensiones_simples_y_compuestas(): void
    {
        $emails = [
            'usuario@gmail.com',
            'usuario@hotmail.com',
            'usuario@outlook.com',
            'adan2018gr@gmail.com.mx',
            'contacto@empresa.com.mx',
            'contacto@empresa.org.mx',
            'usuario@dominio.mx',
            'usuario@institucion.edu',
            'contacto@empresa.org',
            'usuario@empresa.net',
        ];

        foreach ($emails as $email) {
            $validator = Validator::make(
                ['email' => $email],
                ['email' => EmailValidation::formatRules()],
                EmailValidation::messages(),
            );

            $this->assertTrue($validator->passes(), "El email válido fue rechazado: {$email}");
        }
    }

    public function test_rechaza_formatos_invalidos_y_espacios_en_el_email(): void
    {
        $emails = [
            'usuario@gmail',
            'usuario@',
            '@gmail.com',
            'usuario gmail.com',
            'usuario@@gmail.com',
            '"usuario@nombre"@gmail.com',
            'usuario@dominio.',
            'usuario@.com',
            'usuario..nombre@gmail.com',
            'usuario@dominio..com',
            'usuario @gmail.com',
            ' usuario@gmail.com',
            'usuario@gmail.com ',
        ];

        foreach ($emails as $email) {
            $validator = Validator::make(
                ['email' => $email],
                ['email' => EmailValidation::formatRules()],
                EmailValidation::messages(),
            );

            $this->assertFalse($validator->passes(), "El email inválido fue aceptado: {$email}");
        }
    }
}
