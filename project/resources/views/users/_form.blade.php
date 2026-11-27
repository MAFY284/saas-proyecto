@csrf

@if ($user->exists)
    @method('PUT')
@endif

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
    $isAdministrator = auth()->user()->isAdmin();
    $adminSelected = $user->isBootstrapAdmin() || (string) old('is_admin', (int) $isAdmin) === '1';
    $checkboxClass = 'flex min-h-12 cursor-pointer items-center gap-3 rounded-lg border border-gray-300 bg-white px-3 py-2 text-[0.9rem] font-semibold text-gray-700';
    $checkboxInputClass = 'h-4 w-4 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500';
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

    @if ($isAdministrator)
        <fieldset id="participant-types-fields" class="min-w-0 disabled:opacity-60" @disabled($adminSelected)>
            <input type="hidden" name="participant_types_present" value="1">
            @php
                $selectedIds = collect(old(
                    'participant_role_ids',
                    session()->hasOldInput('participant_types_present') ? [] : $selectedParticipantIds,
                ))->filter(fn ($id) => is_int($id) || is_string($id))->map('strval')->all();
            @endphp
            <span id="participant-label" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Tipos de participante</span>
            @if ($participantTypes->isNotEmpty())
                <details class="rounded-lg border border-gray-300 bg-white" aria-labelledby="participant-label" @if ($adminSelected) inert @endif>
                    <summary class="cursor-pointer px-3 py-2 text-sm text-gray-700">Seleccionar tipos</summary>
                    <div class="max-h-56 space-y-2 overflow-y-auto border-t border-gray-200 px-3 py-2">
                        @foreach ($participantTypes as $type)
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="participant_role_ids[]"
                                    value="{{ $type->id }}"
                                    class="{{ $checkboxInputClass }}"
                                    @checked(in_array((string) $type->id, $selectedIds, true))
                                >
                                {{ $type->name }}{{ $type->is_active ? '' : ' (inactivo)' }}
                            </label>
                        @endforeach
                    </div>
                </details>
            @else
                <p class="text-sm text-gray-500">Crea un tipo de participante en Roles para asignarlo aquí.</p>
            @endif
            @error('participant_role_ids')
                <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
            @enderror
            @error('participant_role_ids.*')
                <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
            @enderror
        </fieldset>
    @endif

    <div class="grid grid-cols-1 gap-3 md:col-span-2 sm:grid-cols-2 xl:col-span-3">
        <div>
            <input type="hidden" name="is_active" value="0">
            <label for="is_active" class="{{ $checkboxClass }}">
                <input
                    id="is_active"
                    name="is_active"
                    type="checkbox"
                    value="1"
                    class="{{ $checkboxInputClass }}"
                    @checked((string) old('is_active', $user->exists ? (int) $user->is_active : 1) === '1')
                >
                Usuario activo
            </label>
            @error('is_active')
                <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
            @enderror
        </div>

        @if ($isAdministrator)
            <div>
                <input type="hidden" name="is_admin" value="0">
                <label for="is_admin" class="{{ $checkboxClass }}">
                    <input
                        id="is_admin"
                        name="is_admin"
                        type="checkbox"
                        value="1"
                        class="{{ $checkboxInputClass }}"
                        @checked($adminSelected)
                        @disabled($user->isBootstrapAdmin())
                    >
                    Es administrador (acceso total)
                </label>
                @error('is_admin')
                    <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
                @enderror
            </div>
        @endif
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

@if ($isAdministrator)
    <script>
        {
            const adminCheckbox = document.getElementById('is_admin');
            const participantFields = document.getElementById('participant-types-fields');
            const participantDropdown = participantFields.querySelector('details');

            const updateParticipantFields = () => {
                participantFields.disabled = adminCheckbox.checked;

                if (participantDropdown) {
                    participantDropdown.inert = adminCheckbox.checked;

                    if (adminCheckbox.checked) {
                        participantDropdown.open = false;
                    }
                }
            };

            adminCheckbox.addEventListener('change', updateParticipantFields);
            window.addEventListener('pageshow', updateParticipantFields);
            updateParticipantFields();
        }
    </script>
@endif
