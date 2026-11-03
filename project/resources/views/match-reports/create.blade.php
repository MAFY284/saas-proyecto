@extends('layouts.crud')

@section('title', 'Crear acta')
@section('subtitle', 'Actas')
@section('module', 'Nueva acta')

@section('actions')
    <button
        type="submit"
        form="match-report-form"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
    >
        Guardar
    </button>
    <a
        href="{{ route('match-reports.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@section('content')
    <form id="match-report-form" method="POST" action="{{ route('match-reports.store') }}" class="h-full w-full">
        @include('match-reports._form', ['matchReport' => $matchReport])
    </form>
@endsection
