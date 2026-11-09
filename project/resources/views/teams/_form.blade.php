@csrf

@if ($team->exists)
    @method('PUT')
@endif

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
@endphp

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <!-- Nombre del equipo -->
    <div class="md:col-span-2 xl:col-span-2">
        <label for="name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Nombre del equipo</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $team->name) }}"
            required
            placeholder="Ej. Deportivo Apatzingán"
            class="{{ $fieldClass }}"
        >
        @error('name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Nombre corto / Abreviatura -->
    <div>
        <label for="short_name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Abreviatura / Código</label>
        <input
            id="short_name"
            name="short_name"
            type="text"
            value="{{ old('short_name', $team->short_name) }}"
            placeholder="Ej. APT"
            class="{{ $fieldClass }}"
        >
        @error('short_name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Localidad -->
    <div>
        <label for="locality_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Localidad</label>
        <select id="locality_id" name="locality_id" required class="{{ $fieldClass }}">
            <option value="" disabled hidden>Selecciona una localidad</option>
            @foreach ($localities as $locality)
                <option value="{{ $locality->id }}" @selected(old('locality_id', $team->locality_id) == $locality->id)>
                    {{ $locality->name }}
                </option>
            @endforeach
        </select>
        @error('locality_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Correo de contacto -->
    <div>
        <label for="contact_email" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Correo de contacto</label>
        <input
            id="contact_email"
            name="contact_email"
            type="email"
            value="{{ old('contact_email', $team->contact_email) }}"
            placeholder="correo@ejemplo.com"
            class="{{ $fieldClass }}"
        >
        @error('contact_email')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Teléfono de contacto -->
    <div>
        <label for="contact_phone" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Teléfono de contacto</label>
        <input
            id="contact_phone"
            name="contact_phone"
            type="text"
            value="{{ old('contact_phone', $team->contact_phone) }}"
            placeholder="4431234567"
            class="{{ $fieldClass }}"
        >
        @error('contact_phone')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Estado (Activo / Inactivo) -->
    <div>
        <label for="is_active" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Estado</label>
        <select id="is_active" name="is_active" required class="{{ $fieldClass }}">
            <option value="1" @selected(old('is_active', $team->exists ? $team->is_active : true) == 1)>Activo</option>
            <option value="0" @selected(old('is_active', $team->exists ? $team->is_active : true) == 0)>Inactivo</option>
        </select>
        @error('is_active')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- URL del Logo -->
    <div class="md:col-span-2 xl:col-span-3">
        <label for="logo_url" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">URL del Logo</label>
        <input
            id="logo_url"
            name="logo_url"
            type="url"
            value="{{ old('logo_url', $team->logo_url) }}"
            placeholder="https://ejemplo.com/logo.png"
            class="{{ $fieldClass }}"
        >
        @error('logo_url')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>
</div>