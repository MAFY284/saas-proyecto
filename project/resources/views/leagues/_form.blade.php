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

    @php
        $currentLogoUrl = $league->logoUrl();
    @endphp

    <div class="md:col-span-2 xl:col-span-3">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div
                    class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-gray-200 bg-white"
                    style="width: 96px; height: 96px; min-width: 96px; flex: 0 0 96px; overflow: hidden; border-radius: 16px; display: flex; align-items: center; justify-content: center;"
                >
                    <img
                        id="league-logo-preview"
                        @if ($currentLogoUrl) src="{{ $currentLogoUrl }}" @endif
                        data-original-url="{{ $currentLogoUrl ?? '' }}"
                        alt="Logo de la liga"
                        width="96"
                        height="96"
                        class="h-full w-full object-cover {{ $currentLogoUrl ? '' : 'hidden' }}"
                        style="display: {{ $currentLogoUrl ? 'block' : 'none' }}; width: 96px; height: 96px; max-width: 96px; max-height: 96px; object-fit: cover;"
                    >
                    <div
                        id="league-logo-placeholder"
                        class="flex h-full w-full items-center justify-center text-gray-400 {{ $currentLogoUrl ? 'hidden' : '' }}"
                        style="display: {{ $currentLogoUrl ? 'none' : 'flex' }}; width: 96px; height: 96px; align-items: center; justify-content: center;"
                        aria-hidden="true"
                    >
                        <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#9ca3af" stroke-width="1.5" class="h-11 w-11">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 19 6.5v5.25c0 4.3-2.9 7.15-7 8.5-4.1-1.35-7-4.2-7-8.5V6.5L12 3.75Z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4.5"></path>
                        </svg>
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Logo de la liga</p>
                    <input
                        id="logo"
                        name="logo"
                        type="file"
                        accept="image/*"
                        class="{{ $fieldClass }} text-sm"
                    >
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <button
                            id="league-logo-clear"
                            type="button"
                            class="text-sm font-medium text-red-600 hover:text-red-800 {{ $currentLogoUrl ? '' : 'hidden' }}"
                        >
                            Quitar logo
                        </button>
                        <span class="text-xs text-gray-500">Máximo 2 MB · JPG, PNG o WEBP</span>
                    </div>
                    @if ($league->exists)
                        <input id="remove-logo" type="hidden" name="remove_logo" value="0">
                        <p class="mt-1 text-xs text-gray-500">Puedes mantener el logo, cambiarlo o quitarlo.</p>
                    @endif
                </div>
            </div>
        </div>

        @error('logo')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
        @error('remove_logo')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('logo');
    const preview = document.getElementById('league-logo-preview');
    const placeholder = document.getElementById('league-logo-placeholder');
    const clearButton = document.getElementById('league-logo-clear');
    const removeInput = document.getElementById('remove-logo');

    if (!fileInput || !preview || !placeholder || !clearButton) {
        return;
    }

    const originalUrl = preview.dataset.originalUrl || '';
    let objectUrl = null;

    function releasePreview() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    }

    function showPreview(url) {
        if (url) {
            preview.src = url;
            preview.classList.remove('hidden');
            preview.style.display = 'block';
            placeholder.classList.add('hidden');
            placeholder.style.display = 'none';
        } else {
            preview.removeAttribute('src');
            preview.classList.add('hidden');
            preview.style.display = 'none';
            placeholder.classList.remove('hidden');
            placeholder.style.display = 'flex';
        }
    }

    function updateClearButton() {
        const hasNewFile = fileInput.files && fileInput.files.length > 0;
        const removingOld = removeInput && removeInput.value === '1';

        clearButton.classList.toggle('hidden', !hasNewFile && !originalUrl);
        clearButton.textContent = hasNewFile
            ? 'Quitar selección'
            : removingOld ? 'Deshacer eliminación' : 'Quitar logo';
    }

    fileInput.addEventListener('change', function () {
        releasePreview();
        const file = fileInput.files?.[0];

        if (file) {
            if (removeInput) removeInput.value = '0';
            objectUrl = URL.createObjectURL(file);
            showPreview(objectUrl);
        } else {
            showPreview(removeInput?.value === '1' ? '' : originalUrl);
        }

        updateClearButton();
    });

    clearButton.addEventListener('click', function () {
        const hasNewFile = fileInput.files && fileInput.files.length > 0;

        if (hasNewFile) {
            fileInput.value = '';
            releasePreview();
            showPreview(removeInput?.value === '1' ? '' : originalUrl);
        } else if (originalUrl && removeInput) {
            removeInput.value = removeInput.value === '1' ? '0' : '1';
            showPreview(removeInput.value === '1' ? '' : originalUrl);
        }

        updateClearButton();
    });

    window.addEventListener('pagehide', releasePreview);
    updateClearButton();
});
</script>
@endpush
