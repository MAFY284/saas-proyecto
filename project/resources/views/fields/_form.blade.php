@csrf
@if ($field->exists) @method('PUT') @endif

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
@endphp

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <div>
        <label for="name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Nombre de la Sede</label>
        <input id="name" name="name" type="text" value="{{ old('name', $field->name) }}" required class="{{ $fieldClass }}">
        @error('name')<span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="surface_type" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Tipo de Superficie</label>
        <input id="surface_type" name="surface_type" type="text" value="{{ old('surface_type', $field->surface_type) }}" placeholder="Ej. Pasto sintético, Duela..." class="{{ $fieldClass }}">
        @error('surface_type')<span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="capacity" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Capacidad (Espectadores)</label>
        <input id="capacity" name="capacity" type="number" min="0" value="{{ old('capacity', $field->capacity) }}" class="{{ $fieldClass }}">
        @error('capacity')<span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="status" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Estado</label>
        <select id="status" name="status" required class="{{ $fieldClass }}">
            @foreach (\App\Models\Field::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $field->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>@enderror
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <label for="address" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Dirección completa</label>
        <input id="address" name="address" type="text" value="{{ old('address', $field->address) }}" class="{{ $fieldClass }}">
        @error('address')<span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>@enderror
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <label for="operating_hours" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Horarios de Operación</label>
        <textarea id="operating_hours" name="operating_hours" rows="3" class="{{ $fieldClass }}">{{ old('operating_hours', $field->operating_hours) }}</textarea>
        @error('operating_hours')<span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>@enderror
    </div>
</div>