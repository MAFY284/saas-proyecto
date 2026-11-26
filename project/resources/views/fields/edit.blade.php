@extends('layouts.crud')
@php
    $canWrite = auth()->user()->canAccess('fields', true);
@endphp

@section('title', 'Editar sede')
@section('subtitle', 'Sedes')
@section('module', 'Editar sede')

@section('actions')
    @if ($canWrite)
        <button type="submit" form="field-form" class="inline-flex cursor-pointer items-center justify-center rounded-lg border-0 bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Guardar</button>
    @endif
    <a href="{{ route('fields.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50">Cancelar</a>
@endsection

@if ($canWrite)
@section('content')
    <form id="field-form" method="POST" action="{{ route('fields.update', $field) }}" class="h-full w-full">
        @include('fields._form', ['field' => $field])
    </form>
@endsection
@else
@section('content')
    <p class="text-sm text-gray-600">No tienes permiso de escritura para este módulo.</p>
@endsection
@endif