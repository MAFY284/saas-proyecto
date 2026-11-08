@extends('layouts.crud')

@section('title', 'Editar jugador')
@section('subtitle', 'Jugadores')
@section('module', $player->fullName())

@section('actions')
    <button
        type="submit"
        form="player-form"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
    >
        Guardar
    </button>
    <button
        type="submit"
        form="delete-form"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
        onclick="return confirm('¿Eliminar este jugador?')"
    >
        Eliminar
    </button>
    <a
        href="{{ route('players.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@section('content')
    <form id="player-form" method="POST" action="{{ route('players.update', $player) }}" enctype="multipart/form-data" class="h-full w-full">
        @method('PUT')
        @include('players._form', ['player' => $player])
    </form>

    <form id="delete-form" method="POST" action="{{ route('players.destroy', $player) }}">
        @csrf
        @method('DELETE')
    </form>
@endsection