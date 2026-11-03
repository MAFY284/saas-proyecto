@extends('layouts.crud')

@section('title', 'Editar usuario')
@section('subtitle', 'Usuarios')
@section('module', 'Editar usuario')

@section('actions')
    <button
        type="submit"
        form="user-form"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
    >
        Guardar
    </button>
    <a
        href="{{ route('users.index') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
    >
        Cancelar
    </a>
@endsection

@section('content')
    <form id="user-form" method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="h-full w-full">
        @include('users._form', ['user' => $user])
    </form>
@endsection
