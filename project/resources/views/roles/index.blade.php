@extends('layouts.crud')

@section('title', 'Roles y Permisos')
@section('module', 'Roles y Permisos')

@php
    $canWrite = auth()->user()->canAccess('roles', true);
@endphp

@section('header-search')
    <form method="GET" action="{{ route('roles.index') }}" role="search">
        <label for="crud-search" class="sr-only">Buscar roles</label>
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
                value="{{ $search }}"
                placeholder="Buscar roles por nombre o descripción..."
                class="w-full rounded-lg border border-gray-300 py-2 pr-3 pl-9 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
        </div>
    </form>
@endsection

@if ($canWrite)
@section('actions')
    <a
        href="{{ route('roles.create') }}"
        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-blue-700"
    >
        Crear Rol
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
            class="absolute left-1/2 top-full z-30 mt-1 hidden w-48 -translate-x-1/2 rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
        >
            <button
                type="submit"
                form="bulk-form"
                formaction="{{ route('roles.destroy') }}"
                name="_method"
                value="DELETE"
                id="delete-selected"
                class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50"
                onclick="return confirm('¿Eliminar los roles seleccionados?')"
            >
                Eliminar
            </button>
            <button
                type="submit"
                form="bulk-form"
                formaction="{{ route('roles.process') }}"
                id="process-selected"
                class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
            >
                Ejecutar proceso
            </button>
        </div>
    </div>
@endsection

@endif

@section('header-right')
    @include('pagination.header', ['paginator' => $roles])
@endsection

@section('content')
    @if ($canWrite)
        <form id="bulk-form" method="POST">
            @csrf
        </form>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    @if ($canWrite)
                        <th class="w-10 px-3 py-3">
                            <input
                                id="select-all"
                                type="checkbox"
                                class="rounded border-gray-300"
                                aria-label="Seleccionar todos"
                            >
                        </th>
                    @endif
                    <th class="px-3 py-3">Tipo de participante</th>
                    <th class="px-3 py-3">Descripción</th>
                    <th class="px-3 py-3">Estado</th>
                    <th class="px-3 py-3">Fecha de creación</th>
                    @if ($canWrite)
                        <th class="px-3 py-3 text-right">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr
                        @if ($canWrite)
                            data-edit-url="{{ route('roles.edit', $role) }}"
                        @endif
                        class="role-row border-b border-gray-100 {{ $canWrite ? 'cursor-pointer hover:bg-gray-50' : '' }}"
                    >
                        @if ($canWrite)
                            <td class="px-3 py-3" onclick="event.stopPropagation()">
                                <input
                                    form="bulk-form"
                                    type="checkbox"
                                    name="ids[]"
                                    value="{{ $role->id }}"
                                    class="row-check rounded border-gray-300"
                                >
                            </td>
                        @endif
                        <td class="px-3 py-3 font-medium text-gray-800">
                            <div>{{ $role->name }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $role->slug }}</div>
                        </td>
                        <td class="px-3 py-3 text-gray-600 max-w-xs truncate">
                            {{ $role->description ?: '—' }}
                        </td>
                        <td class="px-3 py-3">
                            @if ($role->is_active)
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-gray-600">
                            {{ $role->created_at?->format('d/m/Y H:i') ?: '—' }}
                        </td>
                        @if ($canWrite)
                            <td class="px-3 py-3 text-right" onclick="event.stopPropagation()">
                                <a
                                    href="{{ route('roles.edit', $role) }}"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-800 mr-2"
                                >
                                    Editar
                                </a>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canWrite ? 6 : 4 }}" class="px-3 py-8 text-center text-gray-500">
                            No hay roles registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@if ($canWrite)
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('select-all');
            const rowChecks = document.querySelectorAll('.row-check');
            const bulkMenu = document.getElementById('bulk-menu');
            const bulkToggle = document.getElementById('bulk-menu-toggle');
            const bulkPanel = document.getElementById('bulk-menu-panel');

            function updateBulkMenu() {
                const count = document.querySelectorAll('.row-check:checked').length;
                if (!bulkMenu) return;

                if (count > 0) {
                    bulkMenu.classList.remove('hidden');
                    bulkMenu.removeAttribute('hidden');
                    if (bulkToggle) {
                        bulkToggle.textContent = 'Acciones (' + count + ')';
                    }
                } else {
                    bulkMenu.classList.add('hidden');
                    bulkMenu.setAttribute('hidden', '');
                    if (bulkPanel) {
                        bulkPanel.classList.add('hidden');
                    }
                    if (bulkToggle) {
                        bulkToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    rowChecks.forEach(cb => { cb.checked = selectAll.checked; });
                    updateBulkMenu();
                });
            }

            rowChecks.forEach(cb => {
                cb.addEventListener('change', function () {
                    if (selectAll) {
                        selectAll.checked = rowChecks.length > 0 && Array.from(rowChecks).every(c => c.checked);
                    }
                    updateBulkMenu();
                });
            });

            if (bulkToggle && bulkPanel) {
                bulkToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isHidden = bulkPanel.classList.toggle('hidden');
                    bulkToggle.setAttribute('aria-expanded', !isHidden);
                });

                document.addEventListener('click', function (e) {
                    if (!bulkMenu.contains(e.target)) {
                        bulkPanel.classList.add('hidden');
                        bulkToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            document.querySelectorAll('.role-row[data-edit-url]').forEach(row => {
                row.addEventListener('click', function (e) {
                    if (e.target.closest('input, button, a')) return;
                    const url = row.getAttribute('data-edit-url');
                    if (url) window.location.href = url;
                });
            });
        });
    </script>
@endpush
@endif

