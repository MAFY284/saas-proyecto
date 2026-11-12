@extends('layouts.app')

@section('title', 'Registro')

@section('content')
    <h1 class="mb-4 text-[1.6rem] font-semibold text-gray-800">Registro</h1>

    <form method="POST" action="{{ route('register') }}" class="text-left">
        @csrf

        <p class="mb-[0.9rem]">
            <label for="name" class="mb-[0.3rem] block text-[0.9rem] font-semibold text-gray-700">Nombre</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                minlength="2"
                maxlength="50"
                required
                class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
        </p>

        <p class="mb-[0.9rem]">
            <label for="email" class="mb-[0.3rem] block text-[0.9rem] font-semibold text-gray-700">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="nombre@gmail.com"
                maxlength="255"
                pattern="[^@\s]+@[^@\s.]+(\.[^@\s.]+)+"
                title="Ingresa un correo con terminación de dominio, por ejemplo nombre@gmail.com o nombre@correo.com.mx."
                required
                autocomplete="email"
                aria-describedby="email-help"
                class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
            <span id="email-help" class="mt-1 block text-xs text-gray-500">Ejemplo: usuario@gmail.com o contacto@empresa.com.mx. La terminación puede ser .com, .mx, .org.mx u otra válida.</span>
        </p>

        <div class="mb-[0.9rem]">
            <label for="password" class="mb-[0.3rem] block text-[0.9rem] font-semibold text-gray-700">Password</label>
            <div style="position: relative">
                <input
                    id="password"
                    name="password"
                    type="password"
                    minlength="8"
                    required
                    aria-describedby="password-requirement"
                    class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] pr-12 font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
                >
                <button
                    type="button"
                    data-password-toggle
                    aria-controls="password"
                    aria-pressed="false"
                    aria-label="Mostrar contraseña"
                    title="Mostrar contraseña"
                    style="position: absolute; top: 0; right: 0; bottom: 0; display: flex; align-items: center"
                    class="cursor-pointer px-3 text-gray-600"
                >
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z" />
                        <circle cx="12" cy="12" r="2.5" />
                        <path data-password-eye-slash stroke-linecap="round" d="m3 3 18 18" />
                    </svg>
                </button>
            </div>
            <p id="password-requirement" class="mt-1 text-xs text-gray-500">Debe tener al menos 8 caracteres.</p>
        </div>

        <div class="mb-[0.9rem]">
            <label for="password_confirmation" class="mb-[0.3rem] block text-[0.9rem] font-semibold text-gray-700">Confirmar password</label>
            <div style="position: relative">
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    aria-describedby="password-confirmation-error"
                    class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] pr-12 font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
                >
                <button
                    type="button"
                    data-password-toggle
                    aria-controls="password_confirmation"
                    aria-pressed="false"
                    aria-label="Mostrar contraseña"
                    title="Mostrar contraseña"
                    style="position: absolute; top: 0; right: 0; bottom: 0; display: flex; align-items: center"
                    class="cursor-pointer px-3 text-gray-600"
                >
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z" />
                        <circle cx="12" cy="12" r="2.5" />
                        <path data-password-eye-slash stroke-linecap="round" d="m3 3 18 18" />
                    </svg>
                </button>
            </div>
            <p id="password-confirmation-error" class="mt-1 hidden text-xs text-red-700" aria-live="polite">Las contraseñas no coinciden.</p>
        </div>

        @if ($errors->any())
            <ul class="mb-[0.9rem] list-disc pl-5 text-left text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <button
            id="register-submit"
            type="submit"
            disabled
            class="mt-[0.4rem] w-full cursor-pointer rounded-lg border-0 bg-gray-900 px-4 py-[0.65rem] font-semibold text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:bg-gray-400 disabled:hover:bg-gray-400"
        >
            Crear cuenta
        </button>
    </form>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.getAttribute('aria-controls'));
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                const isVisible = input.type === 'password';
                input.type = isVisible ? 'text' : 'password';
                toggle.querySelector('[data-password-eye-slash]')?.classList.toggle('hidden', isVisible);
                toggle.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
                toggle.setAttribute('aria-label', isVisible ? 'Ocultar contraseña' : 'Mostrar contraseña');
                toggle.setAttribute('title', isVisible ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        });
    </script>

    <script>
        const registerName = document.getElementById('name');
        const registerEmail = document.getElementById('email');
        const registerPassword = document.getElementById('password');
        const registerPasswordConfirmation = document.getElementById('password_confirmation');
        const passwordConfirmationError = document.getElementById('password-confirmation-error');
        const registerSubmit = document.getElementById('register-submit');

        const updateRegistrationValidity = () => {
            const passwordsMatch = registerPassword.value === registerPasswordConfirmation.value;
            registerPasswordConfirmation.setCustomValidity(
                registerPasswordConfirmation.value && !passwordsMatch ? 'Las contraseñas no coinciden.' : ''
            );
            passwordConfirmationError.classList.toggle(
                'hidden',
                !registerPasswordConfirmation.value || passwordsMatch
            );

            registerSubmit.disabled = !(
                registerName.value.trim() &&
                registerName.validity.valid &&
                registerEmail.validity.valid &&
                registerPassword.validity.valid &&
                registerPasswordConfirmation.validity.valid
            );
        };

        [registerName, registerEmail, registerPassword, registerPasswordConfirmation].forEach((input) => {
            input.addEventListener('input', updateRegistrationValidity);
        });
        updateRegistrationValidity();
    </script>
@endsection
