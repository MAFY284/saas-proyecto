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
            minlength="2"
            maxlength="50"
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
            minlength="2"
            maxlength="50"
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
            minlength="2"
            maxlength="50"
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
            maxlength="255"
            pattern="[^@\s]+@[^@\s.]+(\.[^@\s.]+)+"
            title="Ingresa un correo con terminación de dominio, por ejemplo usuario@gmail.com o usuario@empresa.com.mx."
            required
            autocomplete="email"
            aria-describedby="email-help"
            class="{{ $fieldClass }}"
        >
        <span id="email-help" class="mt-1.5 block text-xs text-gray-500">Ejemplo: usuario@gmail.com o contacto@empresa.com.mx.</span>
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
        <label for="language_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Idioma</label>
        <select id="language_id" name="language_id" required class="{{ $fieldClass }}">
            <option value="" disabled hidden>Selecciona un idioma</option>
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
        <label class="inline-flex items-center gap-2 text-[0.9rem] font-semibold text-gray-700 pb-2 cursor-pointer">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                @checked((string) old('is_active', $user->exists ? (int) $user->is_active : 1) === '1')
            >
            Usuario activo
        </label>
        @error('is_active')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    {{-- Casilla de Administrador --}}
    <div class="flex items-end">
        <input type="hidden" name="is_admin" value="0">
        <label class="inline-flex items-center gap-2 text-[0.9rem] font-semibold text-gray-700 pb-2 cursor-pointer">
            <input
                id="is_admin"
                name="is_admin"
                type="checkbox"
                value="1"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                @checked((string) old('is_admin', !empty($isAdmin) ? 1 : 0) === '1')
                onchange="handleAdminToggle(this.checked)"
            >
            Es Administrador (Acceso total)
        </label>
        @error('is_admin')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    {{-- Tipo de participante en fútbol --}}
    <div>
        <label for="participant_type" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Tipo de participante</label>
        <select
            id="participant_type"
            name="participant_type"
            class="{{ $fieldClass }} transition-colors"
            @disabled((string) old('is_admin', !empty($isAdmin) ? 1 : 0) === '1')
        >
            <option value="">-- Sin tipo asignado --</option>
            @foreach ($participantTypes as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('participant_type', $userParticipantType ?? '') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <span id="participant_type_hint" class="mt-1.5 block text-xs text-gray-500">
            Función dentro de la liga (Jugador, Árbitro, Entrenador, etc.).
        </span>
        @error('participant_type')
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

<script>
    function handleAdminToggle(isAdmin) {
        const select = document.getElementById('participant_type');
        const hint = document.getElementById('participant_type_hint');

        if (select) {
            select.disabled = Boolean(isAdmin);
            if (isAdmin) {
                select.value = '';
                select.classList.add('bg-gray-100', 'cursor-not-allowed', 'opacity-60');
                if (hint) {
                    hint.textContent = 'Menú desactivado: los administradores tienen acceso total y no tienen función de participante de campo.';
                    hint.className = 'mt-1.5 block text-xs text-amber-600 font-medium';
                }
            } else {
                select.classList.remove('bg-gray-100', 'cursor-not-allowed', 'opacity-60');
                if (hint) {
                    hint.textContent = 'Función dentro de la liga (Jugador, Árbitro, Entrenador, etc.).';
                    hint.className = 'mt-1.5 block text-xs text-gray-500';
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const adminCheck = document.getElementById('is_admin');
        if (adminCheck) {
            handleAdminToggle(adminCheck.checked);
            adminCheck.addEventListener('change', function () {
                handleAdminToggle(this.checked);
            });
            adminCheck.addEventListener('click', function () {
                handleAdminToggle(this.checked);
            });
        }
    });
</script>
