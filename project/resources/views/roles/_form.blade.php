@csrf

@if ($role->exists)
    @method('PUT')
@endif

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
@endphp

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <div>
        <label for="name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Tipo de participante</label>
        <input
            id="name"
            name="name"
            value="{{ old('name', $role->name) }}"
            maxlength="100"
            required
            placeholder="Ej. Delegado"
            class="{{ $fieldClass }}"
        >
        @error('name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="slug" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Slug identificador</label>
        <input
            id="slug"
            name="slug"
            value="{{ old('slug', $role->slug) }}"
            placeholder="delegado"
            class="{{ $fieldClass }} bg-gray-50 text-gray-700 font-mono text-sm"
        >
        <span class="mt-1.5 block text-xs text-gray-500">Identificador del rol en el sistema.</span>
        @error('slug')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="flex items-end">
        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center gap-2 text-[0.9rem] font-semibold text-gray-700 pb-2 cursor-pointer">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                @checked((string) old('is_active', $role->exists ? (int) $role->is_active : 1) === '1')
            >
            Tipo activo
        </label>
        @error('is_active')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <label for="description" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Descripción del tipo</label>
        <input
            id="description"
            name="description"
            value="{{ old('description', $role->description) }}"
            placeholder="Ej. Representa al equipo ante la liga."
            class="{{ $fieldClass }}"
        >
        @error('description')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>
</div>
