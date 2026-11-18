<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida que un teléfono tenga 10 dígitos y que su clave LADA
 * corresponda a una clave existente en config('ladas').
 */
class ValidMexicanPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        // Admite el prefijo de país opcional (+52 / 52 / 521).
        if (str_starts_with($digits, '521') && strlen($digits) === 13) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '52') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) !== 10) {
            $fail('El teléfono debe tener 10 dígitos (sin contar la clave de país).');
            return;
        }

        $ladas = config('ladas');
        $twoDigitLada = substr($digits, 0, 2);
        $threeDigitLada = substr($digits, 0, 3);

        $validLada = in_array($twoDigitLada, $ladas['two_digit'], true)
            || in_array($threeDigitLada, $ladas['three_digit'], true);

        if (!$validLada) {
            $fail('La lada del teléfono no corresponde a una clave LADA válida en México.');
        }
    }
}
