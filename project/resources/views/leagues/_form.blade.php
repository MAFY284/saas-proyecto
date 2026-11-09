@csrf

@if ($league->exists)
    @method('PUT')
@endif

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
@endphp

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <div>
        <label for="name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Nombre de la liga</label>
        <input
            id="name"
            name="name"
            value="{{ old('name', $league->name) }}"
            required
            class="{{ $fieldClass }}"
        >
        @error('name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="slug" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Slug</label>
        <input
            id="slug"
            name="slug"
            value="{{ old('slug', $league->slug) }}"
            placeholder="Ej: liga-nacional-mx"
            class="{{ $fieldClass }}"
        >
        <span class="mt-1.5 block text-xs text-gray-500">Identificador URL único. Si lo dejas vacío, se genera desde el nombre.</span>
        @error('slug')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="short_name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Nombre corto / Abreviatura</label>
        <input
            id="short_name"
            name="short_name"
            value="{{ old('short_name', $league->short_name) }}"
            maxlength="100"
            placeholder="Ej: LNM"
            class="{{ $fieldClass }}"
        >
        @error('short_name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="contact_email" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Email de contacto</label>
        <input
            id="contact_email"
            name="contact_email"
            type="email"
            value="{{ old('contact_email', $league->contact_email) }}"
            class="{{ $fieldClass }}"
        >
        @error('contact_email')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="contact_phone" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Teléfono de contacto</label>
        <input
            id="contact_phone"
            name="contact_phone"
            type="tel"
            value="{{ old('contact_phone', $league->contact_phone) }}"
            maxlength="50"
            class="{{ $fieldClass }}"
        >
        @error('contact_phone')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="website_url" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Sitio web</label>
        <input
            id="website_url"
            name="website_url"
            type="url"
            value="{{ old('website_url', $league->website_url) }}"
            placeholder="https://ejemplo.com"
            maxlength="500"
            class="{{ $fieldClass }}"
        >
        @error('website_url')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="country_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">País</label>
        <select id="country_id" name="country_id" required class="{{ $fieldClass }}">
            <option value="">Selecciona un país</option>
            @foreach ($countries as $country)
                <option value="{{ $country->id }}" @selected(old('country_id', $league->country_id) == $country->id)>
                    {{ $country->name }} ({{ $country->iso2_code }})
                </option>
            @endforeach
        </select>
        @error('country_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="currency_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Moneda</label>
        <select id="currency_id" name="currency_id" required class="{{ $fieldClass }}">
            <option value="">Selecciona una moneda</option>
            @foreach ($currencies as $currency)
                <option value="{{ $currency->id }}" @selected(old('currency_id', $league->currency_id) == $currency->id)>
                    {{ $currency->name }} ({{ $currency->code }}{{ $currency->symbol ? ' - '.$currency->symbol : '' }})
                </option>
            @endforeach
        </select>
        @error('currency_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="time_zone" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Zona horaria</label>
        <select id="time_zone" name="time_zone" required class="{{ $fieldClass }}">
            @foreach ($timeZones as $timeZone)
                <option value="{{ $timeZone }}" @selected(old('time_zone', $league->time_zone ?: 'UTC') === $timeZone)>
                    {{ $timeZone }}
                </option>
            @endforeach
        </select>
        @error('time_zone')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="flex items-end">
        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center gap-2 text-[0.9rem] font-semibold text-gray-700">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="rounded border-gray-300"
                @checked((string) old('is_active', $league->exists ? (int) $league->is_active : 1) === '1')
            >
            Liga activa
        </label>
        @error('is_active')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <label for="description" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Descripción</label>
        <textarea
            id="description"
            name="description"
            rows="3"
            class="{{ $fieldClass }}"
            placeholder="Información adicional sobre la liga..."
        >{{ old('description', $league->description) }}</textarea>
        @error('description')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <p class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Logo de la liga</p>
        <p class="mb-3 text-xs text-gray-500">Arrastra una imagen al recuadro o haz clic para seleccionarla. JPG, PNG o WEBP. Máximo 2 MB.</p>
        <div data-image-slots>
            <label
                class="image-slot image-slot--avatar {{ $league->logoUrl() ? 'has-image' : '' }}"
                data-image-slot
                data-slot-index="0"
            >
                <span class="image-slot__label">Logo</span>
                <img
                    class="image-slot__preview {{ $league->logoUrl() ? '' : 'hidden' }}"
                    src="{{ $league->logoUrl() }}"
                    alt=""
                >
                <span class="image-slot__placeholder {{ $league->logoUrl() ? 'hidden' : '' }}">
                    Arrastra o haz clic
                </span>
                <input
                    id="logo"
                    class="sr-only"
                    type="file"
                    name="logo"
                    accept="image/*"
                >
            </label>
        </div>
        @error('logo')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>
</div>
