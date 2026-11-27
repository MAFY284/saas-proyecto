@extends('layouts.crud')

@php
    $canWrite = auth()->user()->canAccess('tournaments', true);
    $canCreate = auth()->user()->canAccessAction('tournaments', 'create');
    $canDelete = auth()->user()->canAccessAction('tournaments', 'delete');
@endphp

@section('title', 'Torneos')
@section('module', 'Torneos')

@section('header-search')
    <form method="GET" action="{{ route('tournaments.index') }}" role="search">
        <label for="crud-search" class="sr-only">Buscar torneos</label>
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
                value="{{ request('q') }}"
                placeholder="Buscar torneos..."
                class="w-full rounded-lg border border-gray-300 py-2 pr-3 pl-9 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
        </div>
    </form>
@endsection

@if ($canCreate)
@section('actions')
    <a
        href="{{ route('tournaments.create') }}"
        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-blue-700"
    >
        Crear Torneo
    </a>
@endsection

@endif

@if ($canDelete)
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
                type="button"
                id="delete-selected"
                class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50"
            >
                Eliminar
            </button>
        </div>
    </div>
@endsection
@endif

@section('header-right')
    @include('pagination.header', ['paginator' => $tournaments])
@endsection

@section('content')
    @if ($canDelete)
        <form id="bulk-form" method="POST" action="{{ route('tournaments.destroy-selected') }}">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    @if ($canDelete)
                        <th class="w-10 px-3 py-3">
                            <input
                                id="select-all"
                                type="checkbox"
                                class="rounded border-gray-300"
                                aria-label="Seleccionar todos"
                            >
                        </th>
                    @endif
                    <th class="px-3 py-3">Nombre</th>
                    <th class="px-3 py-3">Liga</th>
                    <th class="px-3 py-3">Temporada</th>
                    <th class="px-3 py-3">Fecha de Inicio</th>
                    <th class="px-3 py-3">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tournaments as $tournament)
                    <tr
                        @if ($canWrite)
                            data-edit-url="{{ route('tournaments.edit', $tournament) }}"
                        @endif
                        class="tournament-row border-b border-gray-100 {{ $canWrite ? 'cursor-pointer hover:bg-gray-50' : '' }}"
                    >
                        @if ($canDelete)
                            <td class="px-3 py-3" onclick="event.stopPropagation()">
                                <input
                                    form="bulk-form"
                                    type="checkbox"
                                    name="ids[]"
                                    value="{{ $tournament->id }}"
                                    class="row-check rounded border-gray-300"
                                >
                            </td>
                        @endif
                        <td class="px-3 py-3 font-medium text-gray-800">
                            {{ $tournament->name }}
                        </td>
                        <td class="px-3 py-3 text-gray-600">{{ $tournament->league->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-gray-600">{{ $tournament->season ?? '—' }}</td>
                        <td class="px-3 py-3 text-gray-600">{{ $tournament->start_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                {{ $tournament->status === 'active' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                                {{ $tournament->status === 'draft' ? 'bg-gray-50 text-gray-600 ring-gray-500/10' : '' }}
                                {{ $tournament->status === 'finished' ? 'bg-blue-50 text-blue-700 ring-blue-700/10' : '' }}
                                {{ $tournament->status === 'cancelled' ? 'bg-red-50 text-red-700 ring-red-600/10' : '' }}">
                                {{ $tournament->statusLabel() }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canDelete ? 6 : 5 }}" class="px-3 py-8 text-center text-gray-500">No hay torneos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($canDelete)
    {{-- Confirmación propia de la aplicación --}}
    <dialog
        id="confirm-delete-tournament"
        aria-labelledby="confirm-delete-tournament-title"
        aria-describedby="confirm-delete-tournament-description"
        class="rounded-xl border border-gray-200 bg-white p-0 shadow-2xl"
        style="position: fixed; inset: 0; margin: auto; width: min(92vw, 440px); max-height: 85vh; padding: 0; border: 1px solid #e5e7eb; border-radius: 12px; background: white; box-shadow: 0 24px 65px rgba(0,0,0,.25);"
    >
        <div class="border-b border-gray-200 px-6 py-5" style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
            <h2 id="confirm-delete-tournament-title" class="text-lg font-semibold text-gray-900" style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">
                ¿Eliminar torneo?
            </h2>
            <p id="confirm-delete-tournament-description" class="mt-2 text-sm text-gray-600" style="margin: 10px 0 0; font-size: 14px; color: #4b5563;">
                ¿Deseas eliminar <strong id="delete-tournament-count"></strong>?
            </p>
            <p class="mt-2 text-sm text-gray-500" style="margin: 8px 0 0; font-size: 13px; color: #6b7280;">
                Esta acción no se puede deshacer.
            </p>
        </div>
        <div class="flex justify-end gap-3 px-6 py-4" style="display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px;">
            <button
                type="button"
                id="cancel-delete-tournament"
                autofocus
                class="cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                style="padding: 9px 16px; border: 1px solid #d1d5db; border-radius: 8px; background: white; font-size: 14px; font-weight: 600; color: #374151; cursor: pointer;"
            >
                Cancelar
            </button>
            <button
                type="button"
                id="confirm-delete-tournament-button"
                class="cursor-pointer rounded-lg border-0 bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                style="padding: 9px 16px; border: 0; border-radius: 8px; background: #dc2626; font-size: 14px; font-weight: 600; color: white; cursor: pointer;"
            >
                Sí, eliminar
            </button>
        </div>
    </dialog>
    <style>
        #confirm-delete-tournament::backdrop { background: rgba(15, 23, 42, .55); }
    </style>
    @endif
@endsection

@if ($canWrite || $canDelete)
@include('layouts.partials.crud-bulk-scripts')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButton = document.getElementById('delete-selected');
    const deleteModal = document.getElementById('confirm-delete-tournament');
    const deleteCount = document.getElementById('delete-tournament-count');
    const cancelDelete = document.getElementById('cancel-delete-tournament');
    const confirmDelete = document.getElementById('confirm-delete-tournament-button');
    const bulkForm = document.getElementById('bulk-form');
    const bulkPanel = document.getElementById('bulk-menu-panel');
    const bulkToggle = document.getElementById('bulk-menu-toggle');
    const selectAll = document.getElementById('select-all');

    if (!deleteButton || !deleteModal || !deleteCount || !cancelDelete || !confirmDelete || !bulkForm) return;

    let submitting = false;

    const checks = () => [...document.querySelectorAll('.row-check:not(:disabled)')];
    function refreshActions() {
        const count = checks().filter(i => i.checked).length;
        const hasSelection = count > 0;
        if (bulkToggle) {
            bulkToggle.parentElement.hidden = !hasSelection;
            bulkToggle.parentElement.classList.toggle('hidden', !hasSelection);
        }
    }
    
    selectAll?.addEventListener('change', () => {
        checks().forEach((input) => input.checked = selectAll.checked);
        refreshActions();
    });
    
    document.querySelectorAll('.row-check').forEach((input) => {
        input.addEventListener('change', refreshActions);
    });

    document.querySelectorAll('.tournament-row').forEach((row) => {
        row.addEventListener('click', (event) => {
            if (event.target.closest('input')) return;
            window.location.href = row.dataset.editUrl;
        });
    });

    deleteButton.addEventListener('click', function () {
        if (submitting) return;
        const selected = document.querySelectorAll('.row-check:checked:not(:disabled)');
        if (!selected.length) return;

        deleteCount.textContent = selected.length === 1
            ? 'al torneo seleccionado'
            : `a los ${selected.length} torneos seleccionados`;
        bulkPanel?.classList.add('hidden');
        bulkToggle?.setAttribute('aria-expanded', 'false');
        deleteModal.showModal();
    });

    cancelDelete.addEventListener('click', function () {
        deleteModal.close();
    });

    confirmDelete.addEventListener('click', function () {
        if (submitting) return;

        const selected = [...document.querySelectorAll('.row-check:checked:not(:disabled)')];
        if (!selected.length) {
            deleteModal.close();
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = @json(route('tournaments.destroy-selected'));
        form.style.display = 'none';

        const hiddenField = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = String(value);
            form.appendChild(input);
        };

        hiddenField('_token', @json(csrf_token()));
        hiddenField('_method', 'DELETE');
        selected.forEach((checkbox) => hiddenField('ids[]', checkbox.value));

        submitting = true;
        confirmDelete.disabled = true;
        document.body.appendChild(form);
        form.submit();
    });
});
</script>
@endpush
@endif
