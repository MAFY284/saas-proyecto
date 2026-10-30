@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <h1 class="mb-4 text-[1.6rem] font-semibold text-gray-800">Login</h1>

    <form method="POST" action="{{ route('login') }}" class="text-left">
        @csrf

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
            <label class="inline-flex items-center gap-2 text-[0.9rem] font-normal text-gray-700">
                <input type="checkbox" name="remember" class="rounded border-gray-300">
                Recordarme
            </label>
        </p>

        @error('email')
            <p class="mb-[0.9rem] text-[0.9rem] text-red-700">{{ $message }}</p>
        @enderror

        <button
            type="submit"
            class="mt-[0.4rem] w-full cursor-pointer rounded-lg border-0 bg-gray-900 px-4 py-[0.65rem] font-semibold text-white hover:bg-gray-800"
        >
            Entrar
        </button>
    </form>
@endsection
