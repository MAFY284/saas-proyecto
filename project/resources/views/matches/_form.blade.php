@csrf

@if ($game->exists)
    @method('PUT')
@endif

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
@endphp

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <div>
        <label for="tournament_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Torneo</label>
        <select id="tournament_id" name="tournament_id" required class="{{ $fieldClass }}">
            <option value="" disabled hidden>Selecciona un torneo</option>
            @foreach ($tournaments as $tournament)
                <option value="{{ $tournament->id }}" @selected(old('tournament_id', $game->tournament_id) == $tournament->id)>
                    {{ $tournament->name }}
                </option>
            @endforeach
        </select>
        @error('tournament_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="local_team_name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Equipo local</label>
        <input
            id="local_team_name"
            name="local_team_name"
            list="team-options"
            value="{{ old('local_team_name', $game->localTeam?->name) }}"
            placeholder="Escribe o selecciona un equipo"
            required
            class="{{ $fieldClass }}"
        >
        <span class="mt-1.5 block text-xs text-gray-500">Si el equipo no existe, se creará automáticamente.</span>
        @error('local_team_name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="visitor_team_name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Equipo visitante</label>
        <input
            id="visitor_team_name"
            name="visitor_team_name"
            list="team-options"
            value="{{ old('visitor_team_name', $game->visitorTeam?->name) }}"
            placeholder="Escribe o selecciona un equipo"
            required
            class="{{ $fieldClass }}"
        >
        <span class="mt-1.5 block text-xs text-gray-500">Si el equipo no existe, se creará automáticamente.</span>
        @error('visitor_team_name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="field_name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Sede o zona de juego</label>
        <input
            id="field_name"
            name="field_name"
            list="field-options"
            value="{{ old('field_name', $game->field?->name) }}"
            placeholder="Escribe o selecciona una sede"
            required
            class="{{ $fieldClass }}"
        >
        <span class="mt-1.5 block text-xs text-gray-500">Si la sede no existe, se creará automáticamente.</span>
        @error('field_name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="date_time" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Fecha y hora</label>
        <input
            id="date_time"
            name="date_time"
            type="datetime-local"
            value="{{ old('date_time', $game->date_time?->format('Y-m-d\TH:i')) }}"
            required
            class="{{ $fieldClass }}"
        >
        @error('date_time')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="matchday" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Jornada</label>
        <input
            id="matchday"
            name="matchday"
            type="number"
            min="1"
            value="{{ old('matchday', $game->matchday) }}"
            class="{{ $fieldClass }}"
        >
        @error('matchday')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="local_goals" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Goles del local</label>
        <input
            id="local_goals"
            name="local_goals"
            type="number"
            min="0"
            max="999"
            value="{{ old('local_goals', $game->local_goals) }}"
            class="{{ $fieldClass }}"
        >
        <span class="mt-1.5 block text-xs text-gray-500">Déjalo vacío si el partido aún no se ha jugado.</span>
        @error('local_goals')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="visitor_goals" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Goles del visitante</label>
        <input
            id="visitor_goals"
            name="visitor_goals"
            type="number"
            min="0"
            max="999"
            value="{{ old('visitor_goals', $game->visitor_goals) }}"
            class="{{ $fieldClass }}"
        >
        @error('visitor_goals')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="status" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Estado</label>
        <select id="status" name="status" required class="{{ $fieldClass }}">
            @foreach (\App\Models\Game::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $game->status ?: 'scheduled') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <label for="notes" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Notas</label>
        <textarea
            id="notes"
            name="notes"
            rows="4"
            class="{{ $fieldClass }}"
        >{{ old('notes', $game->notes) }}</textarea>
        @error('notes')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>
</div>

<datalist id="team-options">
    @foreach ($teamOptions as $team)
        <option value="{{ $team->name }}"></option>
    @endforeach
</datalist>

<datalist id="field-options">
    @foreach ($fieldOptions as $field)
        <option value="{{ $field->name }}"></option>
    @endforeach
</datalist>
