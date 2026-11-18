@csrf

@if ($refereeCard->exists)
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
                    data-match-date="{{ $match->date_time?->format('Y-m-d') }}"
                    data-field-id="{{ $match->field_id }}"
                    @selected(old('match_id', $refereeCard->match_id) == $match->id)
                >
                    {{ $match->date_time?->format('d/m/Y H:i') }} ·
                    {{ $match->localTeam?->name ?? 'Local' }} vs {{ $match->visitorTeam?->name ?? 'Visitante' }}
                </option>
            @endforeach
        </select>
        <span class="mt-1.5 block text-xs text-gray-500">Solo aparecen partidos sin cédula registrada. Los jugadores salen de la plantilla de cada equipo.</span>
        @error('match_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="field_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Cancha</label>
        <select id="field_id" name="field_id" required class="{{ $fieldClass }}">
            <option value="" disabled hidden>Selecciona una cancha</option>
            @foreach ($fields as $field)
                <option value="{{ $field->id }}" @selected(old('field_id', $refereeCard->field_id) == $field->id)>
                    {{ $field->name }}
                </option>
            @endforeach
        </select>
        @error('field_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="city" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Ciudad</label>
        <input
            id="city"
            name="city"
            type="text"
            maxlength="100"
            value="{{ old('city', $refereeCard->city) }}"
            required
            class="{{ $fieldClass }}"
        >
        @error('city')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="match_date" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Fecha</label>
        <input
            id="match_date"
            name="match_date"
            type="date"
            value="{{ old('match_date', $refereeCard->match_date?->format('Y-m-d')) }}"
            required
            class="{{ $fieldClass }}"
        >
        @error('match_date')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <label for="observations" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Información general</label>
        <textarea
            id="observations"
            name="observations"
            rows="4"
            maxlength="2000"
            placeholder="Observaciones, incidencias o notas del árbitro..."
            class="{{ $fieldClass }}"
        >{{ old('observations', $refereeCard->observations) }}</textarea>
        @error('observations')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="mt-8 border-t border-gray-200 pt-6">
    <div class="mb-4">
        <h2 class="m-0 text-[1.05rem] font-semibold text-gray-800">Jugadores de la cédula</h2>
        <p class="mt-1 text-xs text-gray-500">
            Los jugadores se cargan de la plantilla de cada equipo. Selecciona el partido y marca a los que participaron.
        </p>
    </div>

    @if ($players->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500">
            <p class="m-0">No hay jugadores registrados en el sistema.</p>
            <p class="mt-1 text-xs">Primero registra jugadores y asígnalos a los equipos.</p>
        </div>
    @else
        <div id="players-empty-state" class="hidden rounded-lg border border-dashed border-amber-300 bg-amber-50 px-4 py-6 text-center text-sm text-amber-800">
            <p id="players-empty-select" class="m-0 hidden">Selecciona un partido para habilitar los jugadores de los dos equipos.</p>
            <p id="players-empty-roster" class="m-0 hidden">
                Los equipos de este partido no tienen jugadores registrados.
                <span class="block text-xs font-normal">Primero asigna una plantilla a cada equipo.</span>
                @if (Route::has('players.index'))
                    <a href="{{ route('players.index') }}" class="mt-2 inline-flex font-semibold text-amber-900 underline">
                        Ir a Jugadores
                    </a>
                @endif
            </p>
        </div>

        <div id="players-table-wrapper" class="overflow-x-auto">
            <table class="min-w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                        <th class="w-12 px-3 py-3">Incluir</th>
                        <th class="px-3 py-3">Equipo</th>
                        <th class="px-3 py-3">Jugador</th>
                        <th class="w-20 px-3 py-3 text-center">Goles</th>
                        <th class="w-24 px-3 py-3 text-center">Amarillas</th>
                        <th class="w-28 px-3 py-3 text-center">Roja directa</th>
                        <th class="w-28 px-3 py-3 text-center">Roja indirecta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($players as $player)
                        @php
                            $entry = $entries->get($player->id);
                            $yellowCards = (int) old("players.{$player->id}.yellow_cards", $entry?->yellow_cards ?? 0);
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
                                    max="2"
                                    data-yellow-cards
                                    value="{{ $yellowCards }}"
                                    class="w-full rounded-lg border border-gray-300 px-2 py-1 text-center text-sm"
                                >
                            </td>
                            <td class="px-3 py-2">
                                <input
                                    type="number"
                                    name="players[{{ $player->id }}][direct_red_cards]"
                                    min="0"
                                    max="1"
                                    value="{{ old("players.{$player->id}.direct_red_cards", $entry?->direct_red_cards ?? 0) }}"
                                    class="w-full rounded-lg border border-gray-300 px-2 py-1 text-center text-sm"
                                >
                            </td>
                            <td class="px-3 py-2 text-center">
                                {{-- La roja indirecta no se captura: sale sola con dos amarillas. --}}
                                <span
                                    data-indirect-red
                                    class="inline-flex min-w-9 items-center justify-center rounded-lg px-2 py-1 text-sm font-semibold tabular-nums {{ $yellowCards >= \App\Models\RefereeCardPlayer::YELLOW_CARDS_FOR_INDIRECT_RED ? 'bg-red-100 text-red-700' : 'bg-gray-50 text-gray-500' }}"
                                    title="Se calcula automáticamente: dos amarillas son expulsión"
                                >{{ \App\Models\RefereeCardPlayer::indirectRedFromYellowCards($yellowCards) }}</span>
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
            const dateInput = document.getElementById('match_date');
            const fieldSelect = document.getElementById('field_id');
            const rows = [...document.querySelectorAll('[data-player-row]')];
            const emptyState = document.getElementById('players-empty-state');
            const emptySelect = document.getElementById('players-empty-select');
            const emptyRoster = document.getElementById('players-empty-roster');
            const tableWrapper = document.getElementById('players-table-wrapper');

            // Roja indirecta: se muestra 1 en cuanto el jugador acumula dos amarillas.
            rows.forEach((row) => {
                const yellowInput = row.querySelector('[data-yellow-cards]');
                const indirectOutput = row.querySelector('[data-indirect-red]');

                if (!yellowInput || !indirectOutput) {
                    return;
                }

                yellowInput.addEventListener('input', () => {
                    const expelledByYellows = Number(yellowInput.value || 0) >= 2;

                    indirectOutput.textContent = expelledByYellows ? '1' : '0';
                    indirectOutput.classList.toggle('bg-red-100', expelledByYellows);
                    indirectOutput.classList.toggle('text-red-700', expelledByYellows);
                    indirectOutput.classList.toggle('bg-gray-50', !expelledByYellows);
                    indirectOutput.classList.toggle('text-gray-500', !expelledByYellows);
                });
            });

            function syncPlayers() {
                const option = matchSelect?.selectedOptions?.[0];
                const localTeamId = option?.dataset.localTeamId;
                const visitorTeamId = option?.dataset.visitorTeamId;
                const hasMatch = Boolean(localTeamId && visitorTeamId);
                let visibleCount = 0;

                rows.forEach((row) => {
                    const visible = Boolean(
                        hasMatch
                        && (row.dataset.teamId === localTeamId || row.dataset.teamId === visitorTeamId)
                    );

                    row.classList.toggle('hidden', !visible);

                    if (visible) {
                        visibleCount += 1;
                    }

                    row.querySelectorAll('input').forEach((input) => {
                        input.disabled = !visible;

                        if (!visible && input.type === 'checkbox') {
                            input.checked = false;
                        }
                    });
                });

                if (emptyState) {
                    emptyState.classList.toggle('hidden', visibleCount > 0);
                    emptySelect?.classList.toggle('hidden', !hasMatch);
                    emptyRoster?.classList.toggle('hidden', !hasMatch);
                }

                tableWrapper?.classList.toggle('hidden', visibleCount === 0);
            }

            matchSelect?.addEventListener('change', () => {
                const option = matchSelect.selectedOptions?.[0];

                // La fecha y la cancha se toman del partido elegido.
                if (dateInput && option?.dataset.matchDate) {
                    dateInput.value = option.dataset.matchDate;
                }

                if (fieldSelect && option?.dataset.fieldId) {
                    fieldSelect.value = option.dataset.fieldId;
                }
            });

            matchSelect?.addEventListener('change', syncPlayers);
            syncPlayers();
        });
    </script>
@endpush
