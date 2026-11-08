@csrf

@php
    $person = $player->person;
    $currentTeam = $player->exists ? $player->teams->first() : null;
    $input = 'mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
    $label = 'block text-sm font-medium text-gray-700';
    $typeLabels = [
        'player' => 'Jugador',
        'coach' => 'Entrenador',
        'referee' => 'Árbitro',
        'own' => 'Propio',
    ];
@endphp

<div class="mx-auto grid max-w-3xl gap-6 p-4 sm:grid-cols-2">
    <div>
        <label for="first_name" class="{{ $label }}">Nombre</label>
        <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $person?->first_name) }}" class="{{ $input }}">
        @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="last_name" class="{{ $label }}">Apellido</label>
        <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $person?->last_name) }}" class="{{ $input }}">
        @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="birth_date" class="{{ $label }}">Fecha de nacimiento</label>
        <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date', $person?->birth_date?->format('Y-m-d')) }}" class="{{ $input }}">
        @error('birth_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status" class="{{ $label }}">Estatus</label>
        <select id="status" name="status" class="{{ $input }}">
            @foreach (['active' => 'Activo', 'inactive' => 'Inactivo'] as $value => $text)
                <option value="{{ $value }}" @selected(old('status', $person?->status ?? 'active') === $value)>{{ $text }}</option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="type_id" class="{{ $label }}">Tipo</label>
        <select id="type_id" name="type_id" class="{{ $input }}">
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('type_id', $player->type_id ?? 'player') === $type)>{{ $typeLabels[$type] ?? $type }}</option>
            @endforeach
        </select>
        @error('type_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="team_id" class="{{ $label }}">Equipo</label>
        <select id="team_id" name="team_id" class="{{ $input }}">
            <option value="">Sin equipo</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected((int) old('team_id', $currentTeam?->id) === $team->id)>{{ $team->name }}</option>
            @endforeach
        </select>
        @error('team_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="jersey_number" class="{{ $label }}">Número de camiseta</label>
        <input id="jersey_number" type="number" min="0" max="99" name="jersey_number" value="{{ old('jersey_number', $currentTeam?->pivot?->jersey_number) }}" class="{{ $input }}">
        @error('jersey_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-2">
        <input id="captain" type="checkbox" name="captain" value="1" class="rounded border-gray-300" @checked(old('captain', $currentTeam?->pivot?->captain))>
        <label for="captain" class="text-sm font-medium text-gray-700">Es capitán</label>
    </div>

    <div class="sm:col-span-2">
        <label for="photo" class="{{ $label }}">Foto (máx. 2 MB)</label>
        @if ($player->photo_url)
            <img src="{{ asset('storage/'.$player->photo_url) }}" alt="" class="mt-2 h-20 w-20 rounded-lg object-cover">
        @endif
        <input id="photo" type="file" name="photo" accept="image/*" class="{{ $input }}">
        @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>