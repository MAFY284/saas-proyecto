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
            <label for="user_type_id" class="mb-[0.3rem] block text-[0.9rem] font-semibold text-gray-700">Tipo de usuario</label>
            <select
                id="user_type_id"
                name="user_type_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
                <option value="">Selecciona un tipo</option>
                @foreach ($userTypes as $userType)
                    <option value="{{ $userType->id }}" @selected(old('user_type_id') == $userType->id)>
                        {{ $userType->name }}
                    </option>
                @endforeach
            </select>
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

        <p class="mb-[0.9rem]">
            <label for="password" class="mb-[0.3rem] block text-[0.9rem] font-semibold text-gray-700">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
        </p>

        <p class="mb-[0.9rem]">
            <label for="password_confirmation" class="mb-[0.3rem] block text-[0.9rem] font-semibold text-gray-700">Confirmar password</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
        </p>

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
@endsection
