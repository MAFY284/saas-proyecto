@extends('layouts.crud')

@php
    $canCreate = auth()->user()->canAccessAction('players', 'create');
@endphp

@section('title', 'Crear jugador')
@section('subtitle', 'Jugadores')
@section('module', 'Nuevo jugador')

@section('actions')
    @if ($canCreate)
        <button
            type="submit"
            form="player-form"
            class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
        >
            Guardar
        </button>
    @endif

    <a
        href="{{ route('players.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@if ($canCreate)
@section('content')
    <form
        id="player-form"
        method="POST"
        action="{{ route('players.store') }}"
        enctype="multipart/form-data"
        class="h-full w-full"
    >
        @csrf

               @include('players._form')
    </form>

    @include('players._position_modal')
@endsection
@else
@section('content')
    <p class="text-sm text-gray-600">No tienes permiso de creación para este módulo.</p>
@endsection
@endif
