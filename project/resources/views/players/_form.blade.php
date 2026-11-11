@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';

    $editing = isset($player);
    $person = $editing ? $player->person : null;
    $currentTeam = $editing ? ($selectedTeam ?? $player->teams->first()) : null;
    $currentPositionId = $editing ? ($selectedPositionId ?? null) : null;
    $birthDate = $person?->birth_date
        ? \Illuminate\Support\Carbon::parse($person->birth_date)->format('Y-m-d')
        : '';
    $currentJerseyNumber = $currentTeam?->pivot?->jersey_number;
    $currentCaptain = $currentTeam?->pivot?->captain ?? '0';
    $currentPhotoUrl = $editing && $player->photo_url
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($player->photo_url)
        : null;
@endphp

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <div>
        <label for="first_name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Nombre</label>
        <input id="first_name" name="first_name" type="text"
            value="{{ old('first_name', $person?->first_name ?? '') }}" required class="{{ $fieldClass }}">
        @error('first_name') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="last_name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Apellidos</label>
        <input id="last_name" name="last_name" type="text"
            value="{{ old('last_name', $person?->last_name ?? '') }}" required class="{{ $fieldClass }}">
        @error('last_name') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="birth_date" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Fecha de nacimiento</label>
        <input id="birth_date" name="birth_date" type="date"
            value="{{ old('birth_date', $birthDate) }}" class="{{ $fieldClass }}">
        @error('birth_date') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="status" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Estado</label>
        <select id="status" name="status" class="{{ $fieldClass }}">
            <option value="active" @selected(old('status', $person?->status ?? 'active') === 'active')>Activo</option>
            <option value="inactive" @selected(old('status', $person?->status ?? 'active') === 'inactive')>Inactivo</option>
        </select>
        @error('status') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="team_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Equipo</label>
        <select id="team_id" name="team_id" class="{{ $fieldClass }}">
            <option value="">Sin equipo</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected(old('team_id', $currentTeam?->id ?? '') == $team->id)>{{ $team->name }}</option>
            @endforeach
        </select>
        @error('team_id') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="position_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Posición</label>
        <div class="flex items-center gap-2">
            <select id="position_id" name="position_id" class="{{ $fieldClass }}">
                <option value="">Sin posición</option>
                @foreach ($positions as $position)
                    <option value="{{ $position->id }}" @selected(old('position_id', $currentPositionId ?? '') == $position->id)>{{ $position->name }}</option>
                @endforeach
            </select>
            <button type="button" id="open-position-modal"
                class="shrink-0 cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                + Nueva
            </button>
        </div>
        @error('position_id') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="jersey_number" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Número de camiseta</label>
        <input id="jersey_number" name="jersey_number" type="number" min="1" max="999"
            value="{{ old('jersey_number', $currentJerseyNumber ?? '') }}" class="{{ $fieldClass }}">
        @error('jersey_number') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
    </div>

    {{-- Fotografía: avatar circular, vista previa y opción de quitarla sin borrar al jugador. --}}
    <div>
        <label for="photo" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">
            Fotografía del jugador
        </label>

        <div class="flex items-center gap-4">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-gray-200 bg-gray-100">
                <img
                    id="player-photo-preview"
                    src="{{ $currentPhotoUrl ?? '' }}"
                    data-original-url="{{ $currentPhotoUrl ?? '' }}"
                    alt="Vista previa de la fotografía del jugador"
                    class="h-full w-full object-cover {{ $currentPhotoUrl ? '' : 'hidden' }}"
                >
                <div id="player-photo-placeholder"
                    class="flex h-full w-full items-center justify-center text-gray-400 {{ $currentPhotoUrl ? 'hidden' : '' }}"
                    aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-10 w-10">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path stroke-linecap="round" d="M4.5 21a7.5 7.5 0 0 1 15 0"></path>
                    </svg>
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <input
                    id="photo"
                    name="photo"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp,.gif,.bmp"
                    class="{{ $fieldClass }} text-sm"
                >
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <button
                        id="player-photo-clear"
                        type="button"
                        class="text-sm font-medium text-red-600 hover:text-red-800 {{ $currentPhotoUrl ? '' : 'hidden' }}"
                    >
                        Quitar fotografía
                    </button>
                    <span class="text-xs text-gray-500">Máximo 5 MB · JPG, PNG, WEBP, GIF o BMP</span>
                </div>
                @if ($editing)
                    <input id="remove-photo" type="hidden" name="remove_photo" value="0">
                    <p class="mt-1 text-xs text-gray-500">Puedes mantener la foto, cambiarla o quitarla.</p>
                @endif
            </div>
        </div>

        @error('photo') <span class="mt-1 block text-xs text-red-700">{{ $message }}</span> @enderror
        @error('remove_photo') <span class="mt-1 block text-xs text-red-700">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="captain" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Capitán</label>
        <select id="captain" name="captain" class="{{ $fieldClass }}">
            <option value="0" @selected(old('captain', $currentCaptain) == '0')>No</option>
            <option value="1" @selected(old('captain', $currentCaptain) == '1')>Sí</option>
        </select>
        @error('captain') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('photo');
    const preview = document.getElementById('player-photo-preview');
    const placeholder = document.getElementById('player-photo-placeholder');
    const clearButton = document.getElementById('player-photo-clear');
    const removeInput = document.getElementById('remove-photo');
    if (!fileInput || !preview || !placeholder || !clearButton) return;

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
            placeholder.classList.add('hidden');
        } else {
            preview.removeAttribute('src');
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    }

    function updateClearButton() {
        const hasNewFile = fileInput.files && fileInput.files.length > 0;
        const removingOld = removeInput && removeInput.value === '1';

        clearButton.classList.toggle('hidden', !hasNewFile && !originalUrl);
        clearButton.textContent = hasNewFile
            ? 'Quitar selección'
            : removingOld ? 'Deshacer eliminación' : 'Eliminar foto actual';
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

    // Evita conservar referencias temporales después de salir de la página.
    window.addEventListener('pagehide', releasePreview);
    updateClearButton();
});
</script>
@endpush
