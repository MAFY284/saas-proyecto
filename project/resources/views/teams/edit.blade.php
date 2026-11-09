@extends('layouts.crud')

@section('title', 'Editar equipo')
@section('subtitle', 'Equipos')
@section('module', 'Editar equipo')

@section('actions')
    <button
        type="submit"
        form="team-form"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
    >
        Guardar
    </button>
    <a
        href="{{ route('teams.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@section('content')
    <form id="team-form" method="POST" action="{{ route('teams.update', $team) }}" class="h-full w-full">
        @csrf
        @method('PUT')
        @include('teams._form', ['team' => $team])
    </form>
@endsection