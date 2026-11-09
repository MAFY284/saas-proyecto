@extends('layouts.crud')

@section('title', 'Crear liga')
@section('subtitle', 'Ligas')
@section('module', 'Nueva liga')

@section('actions')
    <button
        type="submit"
        form="league-form"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
    >
        Guardar
    </button>
    <a
        href="{{ route('leagues.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@section('content')
    <form id="league-form" method="POST" action="{{ route('leagues.store') }}" enctype="multipart/form-data" class="h-full w-full">
        @include('leagues._form', ['league' => $league])
    </form>
@endsection

