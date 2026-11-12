@extends('layouts.crud')

@section('title', 'Cédulas arbitrales')
@section('module', 'Cédulas arbitrales')

@section('header-search')
    <form method="GET" action="{{ route('referee-cards.index') }}" role="search">
        <label for="crud-search" class="sr-only">Buscar cédulas arbitrales</label>
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
                placeholder="Buscar por equipo, cancha o ciudad..."
                class="w-full rounded-lg border border-gray-300 py-2 pr-3 pl-9 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
            >
        </div>
    </form>
@endsection

@section('actions')
    <a
        href="{{ route('referee-cards.create') }}"
        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-blue-700"
    >
        Crear Cédula
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
                formaction="{{ route('referee-cards.destroy') }}"
                name="_method"
                value="DELETE"
                id="delete-selected"
                class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50"
                onclick="return confirm('¿Eliminar las cédulas arbitrales seleccionadas?')"
            >
                Eliminar
            </button>
        </div>
    </div>
@endsection

@section('header-right')
    @include('pagination.header', ['paginator' => $cards])
@endsection

@section('content')
    <form id="bulk-form" method="POST">
        @csrf
    </form>

    @if (session('success'))
        <div role="status" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error') || $errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') ?: $errors->first() }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                    <th class="w-10 px-3 py-3">
                        <input
                            id="select-all"
                            type="checkbox"
                            class="rounded border-gray-300"
                            aria-label="Seleccionar todas"
                        >
                    </th>
                    <th class="px-3 py-3">Fecha</th>
                    <th class="px-3 py-3">Partido</th>
                    <th class="px-3 py-3">Cancha</th>
                    <th class="px-3 py-3">Ciudad</th>
                    <th class="px-3 py-3 text-center">Jugadores</th>
                    <th class="px-3 py-3 text-center">Goles</th>
                    <th class="px-3 py-3 text-center">Amarillas</th>
                    <th class="px-3 py-3 text-center">R. directa</th>
                    <th class="px-3 py-3 text-center">R. indirecta</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cards as $card)
                    <tr
                        data-edit-url="{{ route('referee-cards.edit', $card) }}"
                        class="cursor-pointer border-b border-gray-100 hover:bg-gray-50"
                    >
                        <td class="px-3 py-3" onclick="event.stopPropagation()">
                            <input
                                form="bulk-form"
                                type="checkbox"
                                name="ids[]"
                                value="{{ $card->id }}"
                                class="row-check rounded border-gray-300"
                            >
                        </td>
                        <td class="px-3 py-3 text-gray-600">
                            {{ $card->match_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-3 py-3 font-medium text-gray-800">
                            {{ $card->matchLabel() }}
                        </td>
                        <td class="px-3 py-3 text-gray-600">{{ $card->field?->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-gray-600">{{ $card->city }}</td>
                        <td class="px-3 py-3 text-center text-gray-600">{{ $card->entries_count }}</td>
                        <td class="px-3 py-3 text-center font-semibold tabular-nums text-gray-800">
                            {{ $card->total_goals ?? 0 }}
                        </td>
                        <td class="px-3 py-3 text-center tabular-nums text-gray-600">
                            {{ $card->total_yellow ?? 0 }}
                        </td>
                        <td class="px-3 py-3 text-center tabular-nums text-gray-600">
                            {{ $card->total_direct_red ?? 0 }}
                        </td>
                        <td class="px-3 py-3 text-center tabular-nums text-gray-600">
                            {{ $card->total_indirect_red ?? 0 }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-3 py-8 text-center text-gray-500">
                            No hay cédulas arbitrales registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('layouts.partials.crud-bulk-scripts')
@endsection
