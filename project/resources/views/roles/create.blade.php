@extends('layouts.crud')

@section('title', 'Crear rol')
@section('subtitle', 'Roles y Permisos')
@section('module', 'Nuevo rol')

@section('actions')
    <button
        type="submit"
        form="role-form"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
    >
        Guardar
    </button>
    <a
        href="{{ route('roles.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@section('content')
    <form id="role-form" method="POST" action="{{ route('roles.store') }}" class="h-full w-full">
        @include('roles._form')
    </form>
@endsection

