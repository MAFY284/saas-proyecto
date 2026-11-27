@extends('layouts.crud')

@php
    $canCreate = auth()->user()->canAccessAction('teams', 'create');
@endphp

@section('title', 'Crear equipo')
@section('subtitle', 'Equipos')
@section('module', 'Nuevo equipo')

@section('actions')
    @if ($canCreate)
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

@if ($canCreate)
@section('content')
    <form id="team-form" method="POST" action="{{ route('teams.store') }}" enctype="multipart/form-data" class="h-full w-full">
        @include('teams._form', ['team' => new \App\Models\Team()])
    </form>
@endsection
@else
@section('content')
    <p class="text-sm text-gray-600">No tienes permiso de creación para este módulo.</p>
@endsection
@endif