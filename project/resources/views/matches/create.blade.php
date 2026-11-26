@extends('layouts.crud')

@php
    $canCreate = auth()->user()->canAccessAction('matches', 'create');
@endphp

@section('title', 'Crear partido')
@section('subtitle', 'Partidos')
@section('module', 'Nuevo partido')

@section('actions')
    @if ($canCreate)
        <button
            type="submit"
            form="match-form"
            class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
        >
            Guardar
        </button>
    @endif
    <a
        href="{{ route('matches.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@if ($canCreate)
@section('content')
    <form id="match-form" method="POST" action="{{ route('matches.store') }}" class="h-full w-full">
        @include('matches._form', ['game' => $game])
    </form>
@endsection
@else
@section('content')
    <p class="text-sm text-gray-600">No tienes permiso de creación para este módulo.</p>
@endsection
@endif
