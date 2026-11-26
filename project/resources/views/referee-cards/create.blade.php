@extends('layouts.crud')

@php
    $canCreate = auth()->user()->canAccessAction('referee_cards', 'create');
@endphp

@section('title', 'Crear cédula arbitral')
@section('subtitle', 'Cédulas arbitrales')
@section('module', 'Nueva cédula arbitral')

@section('actions')
    @if ($canCreate)
        <button
            type="submit"
            form="referee-card-form"
            class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
        >
            Guardar
        </button>
    @endif
    <a
        href="{{ route('referee-cards.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@if ($canCreate)
@section('content')
    <form id="referee-card-form" method="POST" action="{{ route('referee-cards.store') }}" class="h-full w-full">
        @include('referee-cards._form', ['refereeCard' => $refereeCard])
    </form>
@endsection
@else
@section('content')
    <p class="text-sm text-gray-600">No tienes permiso de creación para este módulo.</p>
@endsection
@endif
