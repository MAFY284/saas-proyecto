@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="mb-4 text-[1.6rem] font-semibold text-gray-800">Zona privada</h1>
    <p class="mb-[0.85rem] text-gray-600">Bienvenido, {{ auth()->user()->name }}.</p>
    <p class="mb-0 text-gray-600">
        <a href="{{ route('users.index') }}" class="font-medium text-blue-600 underline hover:text-blue-700">
            Ir al CRUD de usuarios
        </a>
    </p>
@endsection