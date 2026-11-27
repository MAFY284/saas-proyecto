@extends('layouts.crud')

@section('title', 'Control de acceso')
@section('module', 'Control de acceso')

@php
    $canWrite = auth()->user()->canAccess('roles', true);
@endphp

@if ($canWrite && $participantTypes->isNotEmpty())
    @section('actions')
        <button type="submit" form="access-form" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            Guardar accesos
        </button>
    @endsection
@endif

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



    @if ($errors->any())
        <p role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</p>
    @endif

    @if ($canWrite && $participantTypes->isNotEmpty())
        <form id="access-form" method="POST" action="{{ route('access.update') }}">
            @csrf
            @method('PUT')
        </form>
    @endif

    <div class="space-y-4">
        @forelse ($participantTypes as $type)
            <details
                id="participant-{{ $type->id }}"
                class="participant-details group rounded-xl border border-gray-200 bg-white shadow-sm transition-all overflow-hidden"
            >
                <summary class="flex cursor-pointer list-none items-center justify-between p-5 hover:bg-gray-50/80 transition-colors select-none">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-600 group-open:bg-blue-100 group-open:text-blue-700 font-bold text-sm transition-colors duration-150">
                            {{ mb_substr($type->name, 0, 1) }}
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-semibold text-gray-800">{{ $type->name }}{{ $type->is_active ? '' : ' (inactivo)' }}</h2>
                            </div>
                            <p class="text-xs text-gray-500">Permisos del tipo de participante {{ mb_strtolower($type->name) }}.</p>
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
                    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                        <table class="min-w-full text-left text-sm divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="text-xs uppercase tracking-wide text-gray-500">
                                    <th class="px-4 py-3 font-semibold">Módulo</th>
                                    <th class="px-4 py-3 font-semibold">Columna / Recurso en Base de Datos</th>
                                    <th class="px-4 py-3 font-semibold">Tabla asociada en BD</th>
                                    @foreach ($actions as $label)
                                        <th class="px-4 py-3 text-center font-semibold">{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($resources as $key => $resourceLabel)
                                    @php
                                        $tableName = $resourceTables[$key] ?? $key;
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 transition-colors" data-access-role="{{ $type->id }}" data-access-resource="{{ $key }}">
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
                                                    {{ $type->accessKey($key) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <code class="rounded bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-700">
                                                {{ $tableName }}
                                            </code>
                                        </td>
                                        @foreach ($actions as $action => $label)
                                            @php
                                                $allowed = $type->accessPermission($accessPermissions, $key, $action);
                                            @endphp
                                            <td class="px-4 py-3 text-center">
                                                @if ($canWrite)
                                                    <input
                                                        type="checkbox"
                                                        form="access-form"
                                                        data-access-action="{{ $action }}"
                                                        aria-label="{{ $label }} de {{ $resourceLabel }} para {{ $type->name }}"
                                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        @checked($allowed)
                                                    >
                                                @else
                                                    {{ $allowed ? 'Sí' : 'No' }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </details>
        @empty
            <p class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-600">
                Los tipos que agregues en Roles aparecerán aquí.
            </p>
        @endforelse
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const expandAll = document.getElementById('expand-all-btn');
            const collapseAll = document.getElementById('collapse-all-btn');
            const allDetails = document.querySelectorAll('.participant-details');
            const form = document.getElementById('access-form');

            form?.addEventListener('formdata', (event) => {
                const matrix = {};
                for (const row of document.querySelectorAll('[data-access-role]')) {
                    const inputs = [...row.querySelectorAll('[data-access-action]')];
                    if (!inputs.some(input => input.checked !== input.defaultChecked)) {
                        continue;
                    }

                    const { accessRole, accessResource } = row.dataset;
                    matrix[accessRole] ??= {};
                    matrix[accessRole][accessResource] = Object.fromEntries(
                        inputs.map(input => [input.dataset.accessAction, input.checked])
                    );
                }
                event.formData.set('matrix', JSON.stringify(matrix));
            });

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
