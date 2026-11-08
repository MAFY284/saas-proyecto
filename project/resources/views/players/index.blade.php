@extends('layouts.crud')

@section('title', 'Jugadores')
@section('module', 'Jugadores')

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
                value="{{ $search }}"
                placeholder="Buscar..."
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
                formaction="{{ route('players.destroy') }}"
                name="_method"
                value="DELETE"
                class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50"
                onclick="return confirm('¿Eliminar los jugadores seleccionados?')"
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
    @php
        $typeLabels = [
            'player' => 'Jugador',
            'coach' => 'Entrenador',
            'referee' => 'Árbitro',
            'own' => 'Propio',
        ];
    @endphp

    <form id="bulk-form" method="POST">
        @csrf
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
                    <th class="w-12 px-3 py-3"></th>
                    <th class="px-3 py-3">Nombre</th>
                    <th class="px-3 py-3">Tipo</th>
                    <th class="px-3 py-3">Equipo</th>
                    <th class="px-3 py-3">Estadísticas</th>
                    <th class="px-3 py-3">Fecha de creación</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $player)
                    @php
                        $team = $player->teams->first();
                        $s = $stats[$player->id] ?? null;
                        $matchesPlayed = (int) ($s?->matches_played ?? 0);
                                              $goalsShown = (int) ($s?->total_goals ?? 0);
                    @endphp
                    <tr
                        data-edit-url="{{ route('players.edit', $player) }}"
                        class="player-row cursor-pointer border-b border-gray-100 hover:bg-gray-50"
                    >
                        <td class="px-3 py-3" onclick="event.stopPropagation()">
                            <input
                                form="bulk-form"
                                type="checkbox"
                                name="ids[]"
                                value="{{ $player->id }}"
                                class="row-check rounded border-gray-300"
                            >
                        </td>
                        <td class="px-3 py-3">
                            @if ($player->photo_url)
                                <img
                                    src="{{ asset('storage/'.$player->photo_url) }}"
                                    alt=""
                                    class="h-8 w-8 rounded-full object-cover"
                                >
                            @else
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-600">
                                    {{ mb_strtoupper(mb_substr($player->fullName(), 0, 1)) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 font-medium text-gray-800">{{ $player->fullName() }}</td>
                        <td class="px-3 py-3 text-gray-600">{{ $typeLabels[$player->type_id] ?? '—' }}</td>
                        <td class="px-3 py-3 text-gray-600">
                            @if ($team)
                                {{ $team->name }}@if ($team->pivot->jersey_number !== null) #{{ $team->pivot->jersey_number }}@endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-3 py-3" onclick="event.stopPropagation()">
                            <button
                                type="button"
                                class="stats-btn inline-flex cursor-pointer items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                data-name="{{ $player->fullName() }}"
                                data-matches="{{ $matchesPlayed }}"
                                data-goals="{{ $goalsShown }}"
                                data-yellow="{{ (int) ($s?->total_yellow ?? 0) }}"
                                data-red="{{ (int) ($s?->total_red ?? 0) }}"
                            >
                                Ver estadísticas
                            </button>
                        </td>
                        <td class="px-3 py-3 text-gray-600">{{ $player->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-8 text-center text-gray-500">No hay jugadores registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div
        id="stats-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="stats-title"
    >
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <h2 id="stats-title" class="text-lg font-semibold text-gray-800"></h2>
                <button
                    type="button"
                    id="stats-close"
                    class="cursor-pointer border-0 bg-transparent text-2xl leading-none text-gray-400 hover:text-gray-600"
                    aria-label="Cerrar"
                >&times;</button>
            </div>

            <dl class="mt-4 grid grid-cols-2 gap-3 text-center">
                <div class="rounded-lg bg-gray-50 p-3">
                    <dt class="text-xs uppercase tracking-wide text-gray-500">Partidos</dt>
                    <dd id="stat-matches" class="mt-1 text-2xl font-semibold text-gray-800">0</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-3">
                    <dt class="text-xs uppercase tracking-wide text-gray-500">Goles</dt>
                    <dd id="stat-goals" class="mt-1 text-2xl font-semibold text-gray-800">0</dd>
                </div>
                <div class="rounded-lg bg-yellow-50 p-3">
                    <dt class="text-xs uppercase tracking-wide text-yellow-700">Amarillas</dt>
                    <dd id="stat-yellow" class="mt-1 text-2xl font-semibold text-gray-800">0</dd>
                </div>
                <div class="rounded-lg bg-red-50 p-3">
                    <dt class="text-xs uppercase tracking-wide text-red-700">Rojas</dt>
                    <dd id="stat-red" class="mt-1 text-2xl font-semibold text-gray-800">0</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const selectAll = document.getElementById('select-all');
        const checks = () => [...document.querySelectorAll('.row-check:not(:disabled)')];
        const bulkMenu = document.getElementById('bulk-menu');
        const bulkToggle = document.getElementById('bulk-menu-toggle');
        const bulkPanel = document.getElementById('bulk-menu-panel');
        const statsModal = document.getElementById('stats-modal');

        function setBulkMenuOpen(open) {
            if (!bulkPanel || !bulkToggle) {
                return;
            }

            bulkPanel.classList.toggle('hidden', !open);
            bulkToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function refreshActions() {
            const count = checks().filter((input) => input.checked).length;

            if (bulkMenu) {
                const hasSelection = count > 0;
                bulkMenu.hidden = !hasSelection;
                bulkMenu.classList.toggle('hidden', !hasSelection);

                if (!hasSelection) {
                    setBulkMenuOpen(false);
                }
            }

            if (selectAll) {
                const enabled = checks();
                selectAll.checked = enabled.length > 0 && enabled.every((input) => input.checked);
            }
        }

        function openStats(button) {
            document.getElementById('stats-title').textContent = 'Estadísticas de ' + button.dataset.name;
            document.getElementById('stat-matches').textContent = button.dataset.matches;
            document.getElementById('stat-goals').textContent = button.dataset.goals;
            document.getElementById('stat-yellow').textContent = button.dataset.yellow;
            document.getElementById('stat-red').textContent = button.dataset.red;
            statsModal.classList.remove('hidden');
            statsModal.classList.add('flex');
        }

        function closeStats() {
            statsModal.classList.add('hidden');
            statsModal.classList.remove('flex');
        }

        bulkToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            setBulkMenuOpen(bulkPanel.classList.contains('hidden'));
        });

        document.addEventListener('click', (event) => {
            if (bulkMenu && !bulkMenu.contains(event.target)) {
                setBulkMenuOpen(false);
            }
        });

        selectAll?.addEventListener('change', () => {
            checks().forEach((input) => {
                input.checked = selectAll.checked;
            });
            refreshActions();
        });

        document.querySelectorAll('.row-check').forEach((input) => {
            input.addEventListener('change', refreshActions);
        });

        document.querySelectorAll('.player-row').forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('input')) {
                    return;
                }

                window.location.href = row.dataset.editUrl;
            });
        });

        document.querySelectorAll('.stats-btn').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                openStats(button);
            });
        });

        document.getElementById('stats-close')?.addEventListener('click', closeStats);

        statsModal?.addEventListener('click', (event) => {
            if (event.target === statsModal) {
                closeStats();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeStats();
            }
        });

        refreshActions();
    </script>
@endpush