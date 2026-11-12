@extends('layouts.crud')

@section('title', 'Jugadores')
@section('module', 'Catálogo de jugadores')

@section('header-search')
    <form method="GET" action="{{ route('players.index') }}" role="search">
        <label for="crud-search" class="sr-only">Buscar jugadores</label>

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
                placeholder="Buscar por nombre o apellido..."
                class="w-full rounded-lg border border-gray-300 py-2 pr-3 pl-9 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
        </div>
    </form>
@endsection

@section('actions')
    <a
        href="{{ route('players.create') }}"
        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-blue-700"
    >
        Crear Jugador
    </a>
@endsection

{{-- Misma ubicación y estilo del menú Acciones del CRUD de Usuarios. --}}
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
                type="button"
                id="delete-selected"
                class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50"
            >
                Eliminar
            </button>
        </div>
    </div>
@endsection

@section('header-right')
    @include('pagination.header', ['paginator' => $players])
@endsection

@section('content')
    {{-- Selección múltiple: misma estructura que en Usuarios. --}}
    <form id="bulk-form" method="POST" action="{{ route('players.destroy-selected') }}">
        @csrf
        @method('DELETE')
    </form>
    @if (session('success'))
        <div role="status" class="mx-3 mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error') || $errors->any())
        <div role="alert" class="mx-3 mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') ?: $errors->first() }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <th class="w-10 px-3 py-3">
                        <input id="select-all" type="checkbox" class="rounded border-gray-300" aria-label="Seleccionar todos">
                    </th>
                    <th class="w-12 px-3 py-3"></th>
                    <th class="px-3 py-3">Nombre</th>
                    <th class="px-3 py-3">Equipo</th>
                    <th class="px-3 py-3">Goles</th>
                    <th class="px-3 py-3 text-center">Estado</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($players as $player)
                    <tr
                        data-edit-url="{{ route('players.edit', $player->id) }}"
                        class="cursor-pointer border-b border-gray-100 hover:bg-gray-50"
                    >
                        <td class="px-3 py-3" onclick="event.stopPropagation()">
                            <input
                                form="bulk-form"
                                type="checkbox"
                                name="ids[]"
                                value="{{ $player->id }}"
                                class="row-check rounded border-gray-300"
                                aria-label="Seleccionar a {{ $player->fullName() }}"
                            >
                        </td>
                        <td class="px-3 py-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-600 text-xs font-semibold text-white"
                                    style="width: 36px; height: 36px; min-width: 36px; flex: 0 0 36px; overflow: hidden; border-radius: 50%; display: flex; align-items: center; justify-content: center;"
                                >
                                    @if ($player->photo_url)
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($player->photo_url) }}"
                                            alt="Fotografía de {{ $player->fullName() }}"
                                            width="36"
                                            height="36"
                                            class="h-full w-full object-cover"
                                            style="display: block; width: 36px; height: 36px; max-width: 36px; max-height: 36px; object-fit: cover; border-radius: 50%;"
                                            loading="lazy"
                                        >
                                    @else
                                        @php
                                            $initials = \Illuminate\Support\Str::upper(
                                                \Illuminate\Support\Str::substr($player->person?->first_name ?? 'J', 0, 1)
                                                . \Illuminate\Support\Str::substr($player->person?->last_name ?? '', 0, 1)
                                            );
                                        @endphp
                                        <span aria-hidden="true">{{ $initials }}</span>
                                    @endif
                                </div>
                        </td>
                        <td class="px-3 py-3 font-medium text-gray-800">
                            <a href="{{ route('players.edit', $player->id) }}" class="text-gray-800 no-underline hover:text-blue-700 hover:underline focus:underline" onclick="event.stopPropagation()">
                                {{ $player->fullName() }}
                            </a>
                        </td>
                        <td class="px-3 py-3 text-gray-600">
                            {{ $player->teams->pluck('name')->implode(', ') ?: 'Sin equipo' }}
                        </td>
                        <td class="px-3 py-3 text-gray-600">
                            {{ $player->match_goals ?? 0 }}
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if ($player->person?->status === 'active')
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
                        <td colspan="6" class="px-3 py-8 text-center text-gray-500">
                            No hay jugadores registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Confirmación propia de la aplicación (reemplaza window.confirm). --}}
    <dialog
        id="confirm-delete-player"
        aria-labelledby="confirm-delete-player-title"
        aria-describedby="confirm-delete-player-description"
        class="rounded-xl border border-gray-200 bg-white p-0 shadow-2xl"
        style="position: fixed; inset: 0; margin: auto; width: min(92vw, 440px); max-height: 85vh; padding: 0; border: 1px solid #e5e7eb; border-radius: 12px; background: white; box-shadow: 0 24px 65px rgba(0,0,0,.25);"
    >
        <div class="border-b border-gray-200 px-6 py-5" style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
            <h2 id="confirm-delete-player-title" class="text-lg font-semibold text-gray-900" style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">
                ¿Eliminar jugador?
            </h2>
            <p id="confirm-delete-player-description" class="mt-2 text-sm text-gray-600" style="margin: 10px 0 0; font-size: 14px; color: #4b5563;">
                ¿Deseas eliminar <strong id="delete-player-count"></strong>?
            </p>
            <p class="mt-2 text-sm text-gray-500" style="margin: 8px 0 0; font-size: 13px; color: #6b7280;">
                Esta acción no se puede deshacer. Si alguno tiene registros de partidos, no se eliminará ninguno.
            </p>
        </div>
        <div class="flex justify-end gap-3 px-6 py-4" style="display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px;">
            <button
                type="button"
                id="cancel-delete-player"
                autofocus
                class="cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                style="padding: 9px 16px; border: 1px solid #d1d5db; border-radius: 8px; background: white; font-size: 14px; font-weight: 600; color: #374151; cursor: pointer;"
            >
                Cancelar
            </button>
            <button
                type="button"
                id="confirm-delete-player-button"
                class="cursor-pointer rounded-lg border-0 bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                style="padding: 9px 16px; border: 0; border-radius: 8px; background: #dc2626; font-size: 14px; font-weight: 600; color: white; cursor: pointer;"
            >
                Sí, eliminar
            </button>
        </div>
    </dialog>
    <style>
        #confirm-delete-player::backdrop { background: rgba(15, 23, 42, .55); }
    </style>
@endsection

{{-- Reutiliza exactamente el controlador de selección/menú del CRUD de Usuarios. --}}
@include('layouts.partials.crud-bulk-scripts')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButton = document.getElementById('delete-selected');
    const deleteModal = document.getElementById('confirm-delete-player');
    const deleteCount = document.getElementById('delete-player-count');
    const cancelDelete = document.getElementById('cancel-delete-player');
    const confirmDelete = document.getElementById('confirm-delete-player-button');
    const bulkForm = document.getElementById('bulk-form');
    const bulkPanel = document.getElementById('bulk-menu-panel');
    const bulkToggle = document.getElementById('bulk-menu-toggle');

    if (!deleteButton || !deleteModal || !deleteCount || !cancelDelete ||
        !confirmDelete || !bulkForm) return;

    let submitting = false;

    deleteButton.addEventListener('click', function () {
        if (submitting) return;
        const selected = document.querySelectorAll('.row-check:checked:not(:disabled)');
        if (!selected.length) return;

        deleteCount.textContent = selected.length === 1
            ? 'al jugador seleccionado'
            : `a los ${selected.length} jugadores seleccionados`;
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

        // Crear un formulario real POST + _method DELETE evita que el navegador
        // intente abrir /players/bulk mediante GET (esa ruta no admite GET).
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = @json(route('players.destroy-selected'));
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
