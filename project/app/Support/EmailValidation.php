<?php

namespace App\Support;

use App\Models\User;
use Closure;
use Illuminate\Validation\Rule;

final class EmailValidation
{
    /**
     * @return array<int, mixed>
     */
    public static function formatRules(): array
    {
        return [
            'bail',
            'required',
            'string',
            'max:255',
            static function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_string($value)) {
                    return;
                }

                if (preg_match('/\s/u', $value) === 1) {
                    $fail('El email no debe contener espacios en blanco.');

                    return;
                }

                if (substr_count($value, '@') !== 1) {
                    $fail('El email debe contener exactamente un símbolo @.');

                    return;
                }

                $atPosition = strpos($value, '@');
                $domain = $atPosition === false ? '' : substr($value, $atPosition + 1);

                if ($atPosition !== false && $domain !== '' && ! str_contains($domain, '.')) {
                    $fail('El dominio del email debe incluir una extensión después del punto.');
                }
            },
            Rule::email()->strict(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function rules(?User $user = null): array
    {
        $unique = Rule::unique('users', 'email');

        if ($user !== null) {
            $unique->ignore($user->getKey());
        }

        return [...self::formatRules(), $unique];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'email.required' => 'El email es obligatorio.',
            'email.string' => 'El email debe ser texto.',
            'email.max' => 'El email no puede superar 255 caracteres.',
            'email.email' => 'El email no es válido.',
            'email.unique' => 'El email ya está en uso.',
        ];
    }
}
