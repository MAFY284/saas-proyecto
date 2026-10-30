@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <h1 class="mb-4 text-[1.6rem] font-semibold text-gray-800">Inicio</h1>

    @auth
        <p class="mb-0 text-gray-600">Has iniciado sesión como {{ auth()->user()->name }}.</p>
    @else
        <p class="mb-0 text-gray-600">No has iniciado sesión.</p>
    @endauth
@endsection
