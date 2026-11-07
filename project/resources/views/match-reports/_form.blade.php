@csrf

@if ($matchReport->exists)
    @method('PUT')
@endif

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
    $entries = $entries ?? collect();
@endphp

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <div class="md:col-span-2 xl:col-span-3">
        <label for="match_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Partido</label>
        <select id="match_id" name="match_id" required class="{{ $fieldClass }}">
            <option value="" disabled hidden>Selecciona un partido</option>
            @foreach ($matches as $match)
                <option
                    value="{{ $match->id }}"
                    data-local-team-id="{{ $match->local_team_id }}"
                    data-visitor-team-id="{{ $match->visitor_team_id }}"
                    @selected(old('match_id', $matchReport->match_id) == $match->id)
                >
                    {{ $match->date_time?->format('d/m/Y H:i') }} ·
                    {{ $match->localTeam?->name ?? 'Local' }} vs {{ $match->visitorTeam?->name ?? 'Visitante' }}
                </option>
            @endforeach
        </select>
        <span class="mt-1.5 block text-xs text-gray-500">Solo aparecen partidos sin acta registrada.</span>
        @error('match_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="referee1" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Árbitro principal</label>
        <select id="referee1" name="referee1" required class="{{ $fieldClass }}">
            <option value="" disabled hidden>Selecciona un árbitro</option>
            @foreach ($referees as $referee)
                <option value="{{ $referee->id }}" @selected(old('referee1', $matchReport->referee1) == $referee->id)>
                    {{ $referee->fullName() }}
                </option>
            @endforeach
        </select>
        @error('referee1')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="referee2" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Segundo árbitro</label>
        <select id="referee2" name="referee2" class="{{ $fieldClass }}">
            <option value="">Sin asignar</option>
            @foreach ($referees as $referee)
                <option value="{{ $referee->id }}" @selected(old('referee2', $matchReport->referee2) == $referee->id)>
                    {{ $referee->fullName() }}
                </option>
            @endforeach
        </select>
        @error('referee2')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="referee3" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Tercer árbitro</label>
        <select id="referee3" name="referee3" class="{{ $fieldClass }}">
            <option value="">Sin asignar</option>
            @foreach ($referees as $referee)
                <option value="{{ $referee->id }}" @selected(old('referee3', $matchReport->referee3) == $referee->id)>
                    {{ $referee->fullName() }}
                </option>
            @endforeach
        </select>
        @error('referee3')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="referee4" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Cuarto árbitro</label>
        <select id="referee4" name="referee4" class="{{ $fieldClass }}">
            <option value="">Sin asignar</option>
            @foreach ($referees as $referee)
                <option value="{{ $referee->id }}" @selected(old('referee4', $matchReport->referee4) == $referee->id)>
                    {{ $referee->fullName() }}
                </option>
            @endforeach
        </select>
        @error('referee4')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="match_date" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Fecha y hora</label>
        <input
            id="match_date"
            name="match_date"
            type="datetime-local"
            value="{{ old('match_date', $matchReport->match_date?->format('Y-m-d\TH:i')) }}"
            required
            class="{{ $fieldClass }}"
        >
        @error('match_date')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="local_score" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Goles del local</label>
        <input
            id="local_score"
            name="local_score"
            type="number"
            min="0"
            max="999"
            value="{{ old('local_score', $matchReport->local_score) }}"
            class="{{ $fieldClass }}"
        >
        @error('local_score')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="visitor_score" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Goles del visitante</label>
        <input
            id="visitor_score"
            name="visitor_score"
            type="number"
            min="0"
            max="999"
            value="{{ old('visitor_score', $matchReport->visitor_score) }}"
            class="{{ $fieldClass }}"
        >
        @error('visitor_score')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="status" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Estado</label>
        <select id="status" name="status" required class="{{ $fieldClass }}">
            @foreach (\App\Models\MatchReport::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $matchReport->status ?: 'completed') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <label for="incidents" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Incidencias</label>
        <textarea
            id="incidents"
            name="incidents"
            rows="4"
            class="{{ $fieldClass }}"
        >{{ old('incidents', $matchReport->incidents) }}</textarea>
        @error('incidents')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="mt-8 border-t border-gray-200 pt-6">
    <div class="mb-4">
        <h2 class="m-0 text-[1.05rem] font-semibold text-gray-800">Jugadores del acta</h2>
        <p class="mt-1 text-xs text-gray-500">
            Selecciona el partido para habilitar los jugadores de los dos equipos. Marca a los que participaron.
        </p>
    </div>

    @if ($players->isEmpty())
        <p class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500">
            No hay jugadores registrados.
        </p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                        <th class="w-12 px-3 py-3">Incluir</th>
                        <th class="px-3 py-3">Equipo</th>
                        <th class="px-3 py-3">Jugador</th>
                        <th class="w-20 px-3 py-3 text-center">Titular</th>
                        <th class="w-20 px-3 py-3 text-center">Goles</th>
                        <th class="w-24 px-3 py-3 text-center">Amarillas</th>
                        <th class="w-20 px-3 py-3 text-center">Rojas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($players as $player)
                        @php
                            $entry = $entries->get($player->id);
                        @endphp
                        <tr
                            data-player-row
                            data-team-id="{{ $player->team?->id }}"
                            class="border-b border-gray-100 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2">
                                <input
                                    type="checkbox"
                                    name="players[{{ $player->id }}][selected]"
                                    value="1"
                                    class="rounded border-gray-300"
                                    @checked(old("players.{$player->id}.selected", $entry?->exists))
                                >
                            </td>
                            <td class="px-3 py-2 text-gray-600">{{ $player->team?->name ?? '—' }}</td>
                            <td class="px-3 py-2 font-medium text-gray-800">
                                {{ $player->fullName() }}
                                @if ($player->pivot?->jersey_number)
                                    <span class="text-xs text-gray-400">#{{ $player->pivot->jersey_number }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center">
                                <input
                                    type="checkbox"
                                    name="players[{{ $player->id }}][is_starter]"
                                    value="1"
                                    class="rounded border-gray-300"
                                    @checked(old("players.{$player->id}.is_starter", $entry?->is_starter ?? true))
                                >
                            </td>
                            <td class="px-3 py-2">
                                <input
                                    type="number"
                                    name="players[{{ $player->id }}][goals]"
                                    min="0"
                                    max="99"
                                    value="{{ old("players.{$player->id}.goals", $entry?->goals ?? 0) }}"
                                    class="w-full rounded-lg border border-gray-300 px-2 py-1 text-center text-sm"
                                >
                            </td>
                            <td class="px-3 py-2">
                                <input
                                    type="number"
                                    name="players[{{ $player->id }}][yellow_cards]"
                                    min="0"
                                    max="99"
                                    value="{{ old("players.{$player->id}.yellow_cards", $entry?->yellow_cards ?? 0) }}"
                                    class="w-full rounded-lg border border-gray-300 px-2 py-1 text-center text-sm"
                                >
                            </td>
                            <td class="px-3 py-2">
                                <input
                                    type="number"
                                    name="players[{{ $player->id }}][red_cards]"
                                    min="0"
                                    max="99"
                                    value="{{ old("players.{$player->id}.red_cards", $entry?->red_cards ?? 0) }}"
                                    class="w-full rounded-lg border border-gray-300 px-2 py-1 text-center text-sm"
                                >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @error('players')
        <span class="mt-2 block text-xs text-red-700">{{ $message }}</span>
    @enderror
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const matchSelect = document.getElementById('match_id');
            const rows = [...document.querySelectorAll('[data-player-row]')];

            function syncPlayers() {
                const option = matchSelect?.selectedOptions?.[0];
                const localTeamId = option?.dataset.localTeamId;
                const visitorTeamId = option?.dataset.visitorTeamId;

                rows.forEach((row) => {
                    const visible = Boolean(
                        localTeamId
                        && visitorTeamId
                        && (row.dataset.teamId === localTeamId || row.dataset.teamId === visitorTeamId)
                    );

                    row.classList.toggle('hidden', !visible);

                    row.querySelectorAll('input').forEach((input) => {
                        input.disabled = !visible;

                        if (!visible && input.type === 'checkbox') {
                            input.checked = false;
                        }
                    });
                });
            }

            matchSelect?.addEventListener('change', syncPlayers);
            syncPlayers();
        });
    </script>
@endpush
