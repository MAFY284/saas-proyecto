@extends('layouts.crud')

@php
    $canWrite = auth()->user()->canAccess('teams', true);
@endphp

@section('title', 'Editar equipo')
@section('subtitle', 'Equipos')
@section('module', 'Editar equipo')

@section('actions')
    @if ($canWrite)
        <button
            type="submit"
            form="team-form"
            class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
        >
            Guardar
        </button>
    @endif
    <a
        href="{{ route('teams.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@if ($canWrite)
@section('content')
    <form id="team-form" method="POST" action="{{ route('teams.update', $team) }}" enctype="multipart/form-data" class="h-full w-full">
        @csrf
        @method('PUT')
        @include('teams._form', ['team' => $team])
    </form>
@endsection
@else
@section('content')
    <p class="text-sm text-gray-600">No tienes permiso de escritura para este módulo.</p>
@endsection
@endif