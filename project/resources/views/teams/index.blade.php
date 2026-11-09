@extends('layouts.crud')

@section('title', 'Equipos')
@section('module', 'Catálogo de equipos')

@section('header-search')
    <form method="GET" action="{{ route('teams.index') }}" role="search">
        <label for="crud-search" class="sr-only">Buscar equipos</label>
        <div class="relative">
            <svg
                class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="7" />
                <path stroke-linecap="round" d="M20 20l-3-3" />
            </svg>
            <input
                id="crud-search"
                type="search"
                name="q"
                value="{{ $search ?? '' }}"
                placeholder="Buscar por equipo, abreviatura o localidad..."
                class="w-full rounded-lg border border-gray-300 py-2 pr-3 pl-9 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
        </div>
    </form>
@endsection

@section('actions')
    <a
        href="{{ route('teams.create') }}"
        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-blue-700"
    >
        Crear Equipo
    </a>
@endsection

@section('header-center')
    <div id="bulk-menu" class="relative hidden" hidden>
        <button
            type="button"
            id="bulk-menu-toggle"
            class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
            aria-expanded="false"
            aria-controls="bulk-menu-panel"
            aria-haspopup="true"
        >
            Acciones
        </button>
        <div
            id="bulk-menu-panel"
            class="absolute left-0 top-full z-30 mt-1 hidden w-48 rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
        >
            <button
                type="submit"
                form="bulk-form"
                formaction="{{ route('teams.destroy', 0) }}"
                name="_method"
                value="DELETE"
                id="delete-selected"
                class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50"
                onclick="return confirm('¿Eliminar los equipos seleccionados?')"
            >
                Eliminar
            </button>
        </div>
    </div>
@endsection

@section('header-right')
    @include('pagination.header', ['paginator' => $teams])
@endsection

@section('content')
    <form id="bulk-form" method="POST">
        @csrf
    </form>

    <form method="GET" action="{{ route('teams.index') }}" class="mb-5 flex flex-wrap items-end gap-3">
        <input type="hidden" name="q" value="{{ $search ?? '' }}">

        <div>
            <label for="filter-status" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</label>
            <select
                id="filter-status"
                name="status"
                class="min-w-44 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
                <option value="">Todos</option>
                <option value="1" @selected(($status ?? '') === '1')>Activo</option>
                <option value="0" @selected(($status ?? '') === '0')>Inactivo</option>
            </select>
        </div>

        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
        >
            Filtrar
        </button>

        @if (!empty($search) || !empty($status))
            <a
                href="{{ route('teams.index') }}"
                class="inline-flex items-center justify-center rounded-lg px-2 py-2 text-sm font-semibold text-gray-500 no-underline hover:text-gray-800"
            >
                Limpiar
            </a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <th class="w-10 px-3 py-3">
                        <input
                            id="select-all"
                            type="checkbox"
                            class="rounded border-gray-300"
                            aria-label="Seleccionar todos"
                        >
                    </th>
                    <th class="px-3 py-3">Nombre</th>
                    <th class="px-3 py-3">Abreviatura</th>
                    <th class="px-3 py-3">Localidad</th>
                    <th class="px-3 py-3">Correo</th>
                    <th class="px-3 py-3 text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teams as $team)
                    <tr
                        data-edit-url="{{ route('teams.edit', $team->id) }}"
                        class="cursor-pointer border-b border-gray-100 hover:bg-gray-50"
                    >
                        <td class="px-3 py-3" onclick="event.stopPropagation()">
                            <input
                                form="bulk-form"
                                type="checkbox"
                                name="ids[]"
                                value="{{ $team->id }}"
                                class="row-check rounded border-gray-300"
                            >
                        </td>
                        <td class="px-3 py-3 font-medium text-gray-800">
                            {{ $team->name }}
                        </td>
                        <td class="px-3 py-3 text-gray-600">
                            {{ $team->short_name ?? '—' }}
                        </td>
                        <td class="px-3 py-3 text-gray-600">
                            {{ $team->locality?->name ?? '—' }}
                        </td>
                        <td class="px-3 py-3 text-gray-600">
                            {{ $team->contact_email ?? '—' }}
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if ($team->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-gray-500">No hay equipos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('layouts.partials.crud-bulk-scripts')
@endsection