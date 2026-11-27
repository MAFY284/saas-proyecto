@extends('layouts.crud')

@php
    $canWrite = auth()->user()->canAccess('matches', true);
@endphp

@section('title', 'Editar partido')
@section('subtitle', 'Partidos')
@section('module', 'Editar partido')

@section('actions')
    @if ($canWrite)
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

@if ($canWrite)
@section('content')
    <form id="match-form" method="POST" action="{{ route('matches.update', $game) }}" class="h-full w-full">
        @include('matches._form', ['game' => $game])
    </form>
@endsection
@else
@section('content')
    <p class="text-sm text-gray-600">No tienes permiso de escritura para este módulo.</p>
@endsection
@endif
