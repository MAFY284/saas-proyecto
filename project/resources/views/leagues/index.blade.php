@extends('layouts.crud')

@section('title', 'Ligas')
@section('module', 'Ligas')

@section('header-search')
    <form method="GET" action="{{ route('leagues.index') }}" role="search">
        <label for="crud-search" class="sr-only">Buscar ligas</label>
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
        href="{{ route('leagues.create') }}"
        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-blue-700"
    >
        Crear Liga
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
                formaction="{{ route('leagues.destroy') }}"
                name="_method"
                value="DELETE"
                id="delete-selected"
                class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50"
                onclick="return confirm('¿Eliminar las ligas seleccionadas?')"
            >
                Eliminar
            </button>
            <button
                type="submit"
                form="bulk-form"
                formaction="{{ route('leagues.process') }}"
                id="process-selected"
                class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
            >
                Ejecutar proceso
            </button>
        </div>
    </div>
@endsection

@section('header-right')
    @include('pagination.header', ['paginator' => $leagues])
@endsection

@section('content')
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
                    <th class="px-3 py-3">Email</th>
                    <th class="px-3 py-3">Teléfono</th>
                    <th class="px-3 py-3">Fecha de creación</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leagues as $league)
                    <tr
                        data-edit-url="{{ route('leagues.edit', $league) }}"
                        class="league-row cursor-pointer border-b border-gray-100 hover:bg-gray-50"
                    >
                        <td class="px-3 py-3" onclick="event.stopPropagation()">
                            <input
                                form="bulk-form"
                                type="checkbox"
                                name="ids[]"
                                value="{{ $league->id }}"
                                class="row-check rounded border-gray-300"
                            >
                        </td>
                        <td class="px-3 py-3">
                            <x-league-logo :league="$league" size="sm" />
                        </td>
                        <td class="px-3 py-3 font-medium text-gray-800">
                            <div>{{ $league->name }}</div>
                            @if ($league->short_name)
                                <div class="text-xs text-gray-400">{{ $league->short_name }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-gray-600">{{ $league->contact_email ?? '—' }}</td>
                        <td class="px-3 py-3 text-gray-600">{{ $league->contact_phone ?? '—' }}</td>
                        <td class="px-3 py-3 text-gray-600">{{ $league->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-gray-500">No hay ligas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        const selectAll = document.getElementById('select-all');
        const checks = () => [...document.querySelectorAll('.row-check:not(:disabled)')];
        const bulkMenu = document.getElementById('bulk-menu');
        const bulkToggle = document.getElementById('bulk-menu-toggle');
        const bulkPanel = document.getElementById('bulk-menu-panel');

        function selectedChecks() {
            return checks().filter((input) => input.checked);
        }

        function setBulkMenuOpen(open) {
            if (!bulkPanel || !bulkToggle) {
                return;
            }

            bulkPanel.classList.toggle('hidden', !open);
            bulkToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function refreshActions() {
            const count = selectedChecks().length;

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

        document.querySelectorAll('.league-row').forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('input')) {
                    return;
                }

                window.location.href = row.dataset.editUrl;
            });
        });

        refreshActions();
    </script>
@endpush

