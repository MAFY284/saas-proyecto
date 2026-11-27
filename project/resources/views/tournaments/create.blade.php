@extends('layouts.crud')

@php
    $canCreate = auth()->user()->canAccessAction('tournaments', 'create');
@endphp

@section('title', 'Crear torneo')
@section('subtitle', 'Torneos')
@section('module', 'Nuevo torneo')

@section('actions')
    @if ($canCreate)
        <button
            type="submit"
            form="tournament-form"
            class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
        >
            Guardar
        </button>
    @endif
    <a
        href="{{ route('tournaments.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@if ($canCreate)
@section('content')
    <form id="tournament-form" method="POST" action="{{ route('tournaments.store') }}" class="h-full w-full">
        @csrf

        @php
            $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
        @endphp

        <div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
            
            <!-- Liga Asociada -->
            <div>
                <label for="league_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Liga Asociada</label>
                <select id="league_id" name="league_id" required class="{{ $fieldClass }}">
                    <option value="" disabled hidden {{ old('league_id') ? '' : 'selected' }}>Selecciona una liga</option>
                    @foreach ($leagues as $league)
                        <option value="{{ $league->id }}" @selected(old('league_id') == $league->id)>
                            {{ $league->name }}
                        </option>
                    @endforeach
                </select>
                @error('league_id')
                    <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
                @enderror
            </div>

            <!-- Nombre del Torneo -->
            <div>
                <label for="name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Nombre del Torneo</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                    class="{{ $fieldClass }}"
                >
                @error('name')
                    <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
                @enderror
            </div>

            <!-- Temporada -->
            <div>
                <label for="season" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Temporada (Season)</label>
                <input
                    id="season"
                    name="season"
                    type="text"
                    placeholder="Ej. 2026-I"
                    value="{{ old('season') }}"
                    class="{{ $fieldClass }}"
                >
                @error('season')
                    <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
                @enderror
            </div>

            <!-- Formato -->
            <div>
                <label for="format" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Formato</label>
                <input
                    id="format"
                    name="format"
                    type="text"
                    placeholder="Ej. Liga / Eliminatoria"
                    value="{{ old('format') }}"
                    class="{{ $fieldClass }}"
                >
                @error('format')
                    <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
                @enderror
            </div>

            <!-- Fecha de Inicio -->
            <div>
                <label for="start_date" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Fecha de Inicio</label>
                <input
                    id="start_date"
                    name="start_date"
                    type="date"
                    value="{{ old('start_date') }}"
                    class="{{ $fieldClass }}"
                >
                @error('start_date')
                    <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
                @enderror
            </div>

            <!-- Fecha de Fin -->
            <div>
                <label for="end_date" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Fecha de Fin</label>
                <input
                    id="end_date"
                    name="end_date"
                    type="date"
                    value="{{ old('end_date') }}"
                    class="{{ $fieldClass }}"
                >
                @error('end_date')
                    <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
                @enderror
            </div>

            <!-- Estado -->
            <div>
                <label for="status" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Estado</label>
                <select id="status" name="status" required class="{{ $fieldClass }}">
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', 'draft') == $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
                @enderror
            </div>

        </div>
    </form>
@endsection
@else
@section('content')
    <p class="text-sm text-gray-600">No tienes permiso de creación para este módulo.</p>
@endsection
@endif
