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
    <div class="flex flex-wrap items-center gap-2">
        <a
            href="{{ route('players.create') }}"
            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-blue-700"
        >
            Crear Jugador
        </a>

        <button
            type="button"
            id="edit-selected-player"
            disabled
            class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
        >
            Editar
        </button>

        <form id="delete-selected-player-form" method="POST" action="">
            @csrf
            @method('DELETE')

            <button
                type="submit"
                id="delete-selected-player"
                disabled
                class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40"
            >
                Eliminar
            </button>
        </form>
    </div>
@endsection

@section('header-right')
    @include('pagination.header', ['paginator' => $players])
@endsection

@section('content')
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
                    <th class="px-3 py-3">Nombre</th>
                    <th class="px-3 py-3">Equipo</th>
                    <th class="px-3 py-3">Goles</th>
                    <th class="px-3 py-3 text-center">Estado</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($players as $player)
                    <tr
                        data-player-row
                        data-player-name="{{ $player->fullName() }}"
                        data-edit-url="{{ route('players.edit', $player->id) }}"
                        data-delete-url="{{ route('players.destroy', $player->id) }}"
                        tabindex="0"
                        aria-selected="false"
                        title="Seleccionar jugador"
                        class="cursor-pointer border-b border-gray-100 hover:bg-gray-50 focus:outline focus:outline-2 focus:outline-blue-600"
                    >
                        <td class="px-3 py-3 font-medium text-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-600 text-xs font-semibold text-white">
                                    @if ($player->photo_url)
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($player->photo_url) }}"
                                            alt="Fotografía de {{ $player->fullName() }}"
                                            class="h-full w-full object-cover"
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
                                <span>{{ $player->fullName() }}</span>
                            </div>
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
                        <td colspan="4" class="px-3 py-8 text-center text-gray-500">
                            No hay jugadores registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('[data-player-row]');
    const editButton = document.getElementById('edit-selected-player');
    const deleteButton = document.getElementById('delete-selected-player');
    const deleteForm = document.getElementById('delete-selected-player-form');

    if (!editButton || !deleteButton || !deleteForm) return;

    let selectedRow = null;

    function selectPlayer(row) {
        if (selectedRow) {
            selectedRow.classList.remove('bg-blue-50');
            selectedRow.setAttribute('aria-selected', 'false');
        }

        selectedRow = selectedRow === row ? null : row;

        if (selectedRow) {
            selectedRow.classList.add('bg-blue-50');
            selectedRow.setAttribute('aria-selected', 'true');
            deleteForm.action = selectedRow.dataset.deleteUrl;
        } else {
            deleteForm.removeAttribute('action');
        }

        editButton.disabled = !selectedRow;
        deleteButton.disabled = !selectedRow;
    }

    rows.forEach(function (row) {
        row.addEventListener('click', function () {
            selectPlayer(row);
        });

        row.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectPlayer(row);
            }
        });
    });

    editButton.addEventListener('click', function () {
        if (selectedRow) {
            window.location.assign(selectedRow.dataset.editUrl);
        }
    });

    deleteForm.addEventListener('submit', function (event) {
        if (!selectedRow) {
            event.preventDefault();
            return;
        }

        const confirmed = window.confirm(
            '¿Eliminar al jugador "' + selectedRow.dataset.playerName + '"?\n\n' +
            'Esta acción no se puede deshacer. Si tiene registros de partidos, la eliminación será bloqueada.'
        );

        if (!confirmed) {
            event.preventDefault();
            return;
        }

        deleteButton.disabled = true;
    });
});
</script>
@endpush
