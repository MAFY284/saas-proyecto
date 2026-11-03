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
            <option value="">Selecciona un torneo</option>
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
        <label for="home_team_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Equipo local</label>
        <select id="home_team_id" name="home_team_id" required class="{{ $fieldClass }}">
            <option value="">Selecciona el equipo local</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected(old('home_team_id', $game->home_team_id) == $team->id)>
                    {{ $team->name }}
                </option>
            @endforeach
        </select>
        @error('home_team_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="away_team_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Equipo visitante</label>
        <select id="away_team_id" name="away_team_id" required class="{{ $fieldClass }}">
            <option value="">Selecciona el equipo visitante</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected(old('away_team_id', $game->away_team_id) == $team->id)>
                    {{ $team->name }}
                </option>
            @endforeach
        </select>
        @error('away_team_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="venue_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Sede</label>
        <select id="venue_id" name="venue_id" class="{{ $fieldClass }}">
            <option value="">Sin sede asignada</option>
            @foreach ($venues as $venue)
                <option value="{{ $venue->id }}" @selected(old('venue_id', $game->venue_id) == $venue->id)>
                    {{ $venue->name }}
                </option>
            @endforeach
        </select>
        @error('venue_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="referee_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Árbitro</label>
        <select id="referee_id" name="referee_id" class="{{ $fieldClass }}">
            <option value="">Sin árbitro asignado</option>
            @foreach ($referees as $referee)
                <option value="{{ $referee->id }}" @selected(old('referee_id', $game->referee_id) == $referee->id)>
                    {{ $referee->fullName() }}
                </option>
            @endforeach
        </select>
        @error('referee_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="match_date" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Fecha y hora</label>
        <input
            id="match_date"
            name="match_date"
            type="datetime-local"
            value="{{ old('match_date', $game->match_date?->format('Y-m-d\TH:i')) }}"
            required
            class="{{ $fieldClass }}"
        >
        @error('match_date')
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
        <label for="home_goals" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Goles del local</label>
        <input
            id="home_goals"
            name="home_goals"
            type="number"
            min="0"
            max="999"
            value="{{ old('home_goals', $game->home_goals) }}"
            class="{{ $fieldClass }}"
        >
        <span class="mt-1.5 block text-xs text-gray-500">Déjalo vacío si el partido aún no se ha jugado.</span>
        @error('home_goals')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="away_goals" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Goles del visitante</label>
        <input
            id="away_goals"
            name="away_goals"
            type="number"
            min="0"
            max="999"
            value="{{ old('away_goals', $game->away_goals) }}"
            class="{{ $fieldClass }}"
        >
        @error('away_goals')
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
