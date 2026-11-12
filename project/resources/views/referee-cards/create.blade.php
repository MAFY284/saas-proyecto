@extends('layouts.crud')

@section('title', 'Crear cédula arbitral')
@section('subtitle', 'Cédulas arbitrales')
@section('module', 'Nueva cédula arbitral')

@section('actions')
    <button
        type="submit"
        form="referee-card-form"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
    >
        Guardar
    </button>
    <a
        href="{{ route('referee-cards.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@section('content')
    <form id="referee-card-form" method="POST" action="{{ route('referee-cards.store') }}" class="h-full w-full">
        @include('referee-cards._form', ['refereeCard' => $refereeCard])
    </form>
@endsection
