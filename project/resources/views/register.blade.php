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
                value="{{ old('name') }}"
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
                required
                class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
        </p>

        <div class="mb-[0.9rem]">
            <label for="password" class="mb-[0.3rem] block text-[0.9rem] font-semibold text-gray-700">Password</label>
            <div class="relative">
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] pr-12 font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
                >
                <button
                    type="button"
                    data-password-toggle
                    aria-controls="password"
                    aria-pressed="false"
                    aria-label="Mostrar contraseña"
                    title="Mostrar contraseña"
                    class="absolute inset-y-0 right-0 flex cursor-pointer items-center px-3 text-gray-600"
                >
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z" />
                        <circle cx="12" cy="12" r="2.5" />
                        <path data-password-eye-slash stroke-linecap="round" d="m3 3 18 18" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="mb-[0.9rem]">
            <label for="password_confirmation" class="mb-[0.3rem] block text-[0.9rem] font-semibold text-gray-700">Confirmar password</label>
            <div class="relative">
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] pr-12 font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
                >
                <button
                    type="button"
                    data-password-toggle
                    aria-controls="password_confirmation"
                    aria-pressed="false"
                    aria-label="Mostrar contraseña"
                    title="Mostrar contraseña"
                    class="absolute inset-y-0 right-0 flex cursor-pointer items-center px-3 text-gray-600"
                >
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z" />
                        <circle cx="12" cy="12" r="2.5" />
                        <path data-password-eye-slash stroke-linecap="round" d="m3 3 18 18" />
                    </svg>
                </button>
            </div>
        </div>

        @if ($errors->any())
            <ul class="mb-[0.9rem] list-disc pl-5 text-left text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <button
            type="submit"
            class="mt-[0.4rem] w-full cursor-pointer rounded-lg border-0 bg-gray-900 px-4 py-[0.65rem] font-semibold text-white hover:bg-gray-800"
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
@endsection
