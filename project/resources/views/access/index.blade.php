@extends('layouts.crud')

@section('title', 'Control de acceso')
@section('module', 'Control de acceso')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-600">Define qué puede consultar o modificar cada tipo de participante dentro de la liga.</p>
            <p class="text-xs text-gray-500 mt-0.5">Cada participante cuenta con su sección desplegable con el mapeo a su columna en la base de datos.</p>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                id="expand-all-btn"
                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
            >
                Desplegar todos
            </button>
            <button
                type="button"
                id="collapse-all-btn"
                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
            >
                Contraer todos
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    {{-- Selector desplegable rápido de tipo de participante --}}
    <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50/50 p-4">
        <label for="participant-quick-select" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-blue-900">
            Seleccionar tipo de participante (Desplegable)
        </label>
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <select
                id="participant-quick-select"
                class="w-full sm:w-72 rounded-lg border border-blue-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-blue-600 focus:outline-none"
            >
                <option value="">-- Elige un tipo para desplegar --</option>
                @foreach ($participantTypes as $type => $label)
                    <option value="participant-{{ $type }}" @selected($type === 'jugador')>
                        {{ $label }} {{ $type === 'jugador' ? '(Jugador principal)' : '' }}
                    </option>
                @endforeach
            </select>
            <span class="text-xs text-blue-700">
                Abre y enfoca la configuración del participante seleccionado.
            </span>
        </div>
    </div>

    <div class="space-y-4">
        @foreach ($participantTypes as $type => $label)
            <details
                id="participant-{{ $type }}"
                class="participant-details group rounded-xl border border-gray-200 bg-white shadow-sm transition-all overflow-hidden"
                @if ($type === 'jugador') open @endif
            >
                <summary class="flex cursor-pointer list-none items-center justify-between p-5 hover:bg-gray-50/80 transition-colors select-none">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $type === 'jugador' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }} font-bold text-sm">
                            {{ mb_substr($label, 0, 1) }}
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-semibold text-gray-800">{{ $label }}</h2>
                                @if ($type === 'jugador')
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 border border-blue-200">
                                        Principal
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500">Permisos del tipo de participante {{ mb_strtolower($label) }}.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-400 group-open:hidden">Clic para desplegar</span>
                        <span class="text-xs text-gray-400 hidden group-open:inline">Clic para contraer</span>
                        <svg
                            class="h-5 w-5 text-gray-500 transition-transform duration-200 group-open:rotate-180"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </summary>

                <div class="border-t border-gray-200 p-5 bg-gray-50/30">
                    <form method="POST" action="{{ route('access.update', $type) }}" class="overflow-x-auto">
                        @csrf
                        @method('PUT')

                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <span class="text-xs text-gray-500">
                                Mapeo de accesos para <strong>{{ $label }}</strong> vinculado a los módulos del sistema y columnas en base de datos.
                            </span>
                            <button
                                type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm"
                            >
                                Guardar accesos para {{ $label }}
                            </button>
                        </div>

                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                            <table class="min-w-full text-left text-sm divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr class="text-xs uppercase tracking-wide text-gray-500">
                                        <th class="px-4 py-3 font-semibold">Módulo</th>
                                        <th class="px-4 py-3 font-semibold">Columna / Recurso en Base de Datos</th>
                                        <th class="px-4 py-3 font-semibold">Tabla asociada en BD</th>
                                        <th class="px-4 py-3 text-center font-semibold">Lectura (can_read)</th>
                                        <th class="px-4 py-3 text-center font-semibold">Escritura (can_write)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($resources as $key => $resourceLabel)
                                        @php
                                            $permission = $accessPermissions->get("access:{$type}:{$key}");
                                            $tableName = $resourceTables[$key] ?? $key;
                                        @endphp
                                        <tr class="hover:bg-gray-50/60 transition-colors">
                                            <td class="px-4 py-3 font-medium text-gray-800">
                                                {{ $resourceLabel }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex flex-col">
                                                    <span class="inline-flex items-center gap-1">
                                                        <span class="text-xs text-gray-500 font-mono">resource:</span>
                                                        <code class="rounded bg-blue-50 px-2 py-0.5 text-xs font-mono font-semibold text-blue-700 border border-blue-100">
                                                            {{ $key }}
                                                        </code>
                                                    </span>
                                                    <span class="text-[11px] text-gray-400 font-mono mt-0.5">
                                                        access:{{ $type }}:{{ $key }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <code class="rounded bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-700">
                                                    {{ $tableName }}
                                                </code>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="hidden" name="permissions[{{ $key }}][read]" value="0">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[{{ $key }}][read]"
                                                    value="1"
                                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                    @checked($permission?->can_read)
                                                >
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="hidden" name="permissions[{{ $key }}][write]" value="0">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[{{ $key }}][write]"
                                                    value="1"
                                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                    @checked($permission?->can_write)
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button
                                type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm"
                            >
                                Guardar accesos para {{ $label }}
                            </button>
                        </div>
                    </form>
                </div>
            </details>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('participant-quick-select');
            const expandAll = document.getElementById('expand-all-btn');
            const collapseAll = document.getElementById('collapse-all-btn');
            const allDetails = document.querySelectorAll('.participant-details');

            if (select) {
                select.addEventListener('change', function () {
                    const targetId = this.value;
                    if (!targetId) return;

                    const target = document.getElementById(targetId);
                    if (target) {
                        target.open = true;
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            }

            if (expandAll) {
                expandAll.addEventListener('click', function () {
                    allDetails.forEach(d => d.open = true);
                });
            }

            if (collapseAll) {
                collapseAll.addEventListener('click', function () {
                    allDetails.forEach(d => d.open = false);
                });
            }
        });
    </script>
@endsection
