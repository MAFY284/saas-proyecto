@csrf

@if ($user->exists)
    @method('PUT')
@endif

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
@endphp

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <div>
        <label for="first_name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Nombre</label>
        <input
            id="first_name"
            name="first_name"
            value="{{ old('first_name', $user->first_name) }}"
            required
            class="{{ $fieldClass }}"
        >
        @error('first_name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="last_name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Apellido</label>
        <input
            id="last_name"
            name="last_name"
            value="{{ old('last_name', $user->last_name) }}"
            required
            class="{{ $fieldClass }}"
        >
        @error('last_name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Nombre visible</label>
        <input
            id="name"
            name="name"
            value="{{ old('name', $user->name) }}"
            required
            class="{{ $fieldClass }}"
        >
        <span class="mt-1.5 block text-xs text-gray-500">Se muestra en el listado y en la cuenta.</span>
        @error('name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $user->email) }}"
            required
            class="{{ $fieldClass }}"
        >
        @error('email')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="phone" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Teléfono</label>
        <input
            id="phone"
            name="phone"
            type="tel"
            value="{{ old('phone', $user->phone) }}"
            maxlength="50"
            class="{{ $fieldClass }}"
        >
        @error('phone')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="password" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Password</label>
        <input
            id="password"
            name="password"
            type="password"
            @unless ($user->exists) required @endunless
            autocomplete="new-password"
            class="{{ $fieldClass }}"
        >
        @if ($user->exists)
            <span class="mt-1.5 block text-xs text-gray-500">Déjalo vacío para conservar el password actual.</span>
        @endif
        @error('password')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="user_type_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Tipo de usuario</label>
        <select id="user_type_id" name="user_type_id" class="{{ $fieldClass }}">
            <option value="">Sin tipo</option>
            @foreach ($userTypes as $userType)
                <option value="{{ $userType->id }}" @selected(old('user_type_id', $user->user_type_id) == $userType->id)>
                    {{ $userType->name }}
                </option>
            @endforeach
        </select>
        @error('user_type_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="language_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Idioma</label>
        <select id="language_id" name="language_id" class="{{ $fieldClass }}">
            <option value="">Sin idioma</option>
            @foreach ($languages as $language)
                <option value="{{ $language->id }}" @selected(old('language_id', $user->language_id) == $language->id)>
                    {{ $language->name }}
                </option>
            @endforeach
        </select>
        @error('language_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="time_zone" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Zona horaria</label>
        <select id="time_zone" name="time_zone" required class="{{ $fieldClass }}">
            @foreach ($timeZones as $timeZone)
                <option value="{{ $timeZone }}" @selected(old('time_zone', $user->time_zone ?: 'UTC') === $timeZone)>
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
                @checked((string) old('is_active', $user->exists ? (int) $user->is_active : 1) === '1')
            >
            Usuario activo
        </label>
        @error('is_active')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <p class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Imagen de perfil</p>
        <p class="mb-3 text-xs text-gray-500">Arrastra una imagen al recuadro o haz clic para seleccionarla. JPG, PNG o WEBP. Máximo 2 MB.</p>
        <div data-image-slots>
            <label
                class="image-slot image-slot--avatar {{ $user->avatarUrl() ? 'has-image' : '' }}"
                data-image-slot
                data-slot-index="0"
            >
                <span class="image-slot__label">Foto</span>
                <img
                    class="image-slot__preview {{ $user->avatarUrl() ? '' : 'hidden' }}"
                    src="{{ $user->avatarUrl() }}"
                    alt=""
                >
                <span class="image-slot__placeholder {{ $user->avatarUrl() ? 'hidden' : '' }}">
                    Arrastra o haz clic
                </span>
                <input
                    id="avatar"
                    class="sr-only"
                    type="file"
                    name="avatar"
                    accept="image/*"
                >
            </label>
        </div>
        @error('avatar')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>
</div>
