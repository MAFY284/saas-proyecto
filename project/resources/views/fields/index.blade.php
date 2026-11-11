@extends('layouts.crud')

@section('title', 'Sedes')
@section('module', 'Sedes')

@section('header-search')
    <form method="GET" action="{{ route('fields.index') }}" role="search">
        <label for="crud-search" class="sr-only">Buscar sedes</label>
        <div class="relative">
            <svg class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7" />
                <path stroke-linecap="round" d="M20 20l-3-3" />
            </svg>
            <input id="crud-search" type="search" name="q" value="{{ $search }}" placeholder="Buscar por nombre o dirección..." class="w-full rounded-lg border border-gray-300 py-2 pr-3 pl-9 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600">
        </div>
    </form>
@endsection

@section('actions')
    <a href="{{ route('fields.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-blue-700">
        Crear Sede
    </a>
@endsection

@section('header-center')
    <div id="bulk-menu" class="relative hidden" hidden>
        <button type="button" id="bulk-menu-toggle" class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50" aria-expanded="false" aria-controls="bulk-menu-panel" aria-haspopup="true">
            Acciones
        </button>
        <div id="bulk-menu-panel" class="absolute left-0 top-full z-30 mt-1 hidden w-48 rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
            <button type="submit" form="bulk-form" formaction="{{ route('fields.destroy') }}" name="_method" value="DELETE" id="delete-selected" class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50" onclick="return confirm('¿Eliminar las sedes seleccionadas?')">
                Eliminar
            </button>
        </div>
    </div>
@endsection

@section('header-right')
    @include('pagination.header', ['paginator' => $fields])
@endsection

@section('content')
    <form id="bulk-form" method="POST">@csrf</form>

    <form method="GET" action="{{ route('fields.index') }}" class="mb-5 flex flex-wrap items-end gap-3">
        <input type="hidden" name="q" value="{{ $search }}">
        <div>
            <label for="filter-status" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</label>
            <select id="filter-status" name="status" class="min-w-44 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600">
                <option value="">Todos</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Filtrar
        </button>
        @if ($search !== '' || $status !== '')
            <a href="{{ route('fields.index') }}" class="inline-flex items-center justify-center rounded-lg px-2 py-2 text-sm font-semibold text-gray-500 no-underline hover:text-gray-800">
                Limpiar
            </a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <th class="w-10 px-3 py-3"><input id="select-all" type="checkbox" class="rounded border-gray-300"></th>
                    <th class="px-3 py-3">Nombre</th>
                    <th class="px-3 py-3">Superficie</th>
                    <th class="px-3 py-3">Capacidad</th>
                    <th class="px-3 py-3">Dirección</th>
                    <th class="px-3 py-3">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($fields as $field)
                    <tr data-edit-url="{{ route('fields.edit', $field) }}" class="cursor-pointer border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-3 py-3" onclick="event.stopPropagation()">
                            <input form="bulk-form" type="checkbox" name="ids[]" value="{{ $field->id }}" class="row-check rounded border-gray-300">
                        </td>
                        <td class="px-3 py-3 font-medium text-gray-800">{{ $field->name }}</td>
                        <td class="px-3 py-3 text-gray-600">{{ $field->surface_type ?? '—' }}</td>
                        <td class="px-3 py-3 text-gray-600">{{ $field->capacity ?? '—' }}</td>
                        <td class="px-3 py-3 text-gray-600">{{ $field->address ?? '—' }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $field->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $field->statusLabel() }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-8 text-center text-gray-500">No hay sedes registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('layouts.partials.crud-bulk-scripts')
@endsection