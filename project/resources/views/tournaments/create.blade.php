@extends('layouts.crud')

@section('title', 'Nuevo Torneo')
@section('module', 'Crear Torneo')

@section('content')
    <div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Registrar Nuevo Torneo</h1>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6 text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('tournaments.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Liga Asociada</label>
                <select name="league_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border text-sm" required>
                    <option value="">Selecciona una liga</option>
                    @foreach($leagues as $league)
                        <option value="{{ $league->id }}" {{ old('league_id') == $league->id ? 'selected' : '' }}>
                            {{ $league->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Torneo</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border text-sm" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Temporada (Season)</label>
                    <input type="text" name="season" value="{{ old('season') }}" placeholder="Ej. 2026-I" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Formato</label>
                    <input type="text" name="format" value="{{ old('format') }}" placeholder="Ej. Liga / Eliminatoria" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Fin</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border text-sm" required>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ old('status', 'draft') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('tournaments.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold">Guardar Torneo</button>
            </div>
        </form>
    </div>
@endsection