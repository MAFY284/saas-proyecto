<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Game;
use App\Models\Player;
use App\Models\RefereeCard;
use App\Models\RefereeCardPlayer;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RefereeCardController extends Controller
{
    /**
     * Listado de cédulas arbitrales.
     */
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->get('q', '')), 0, 100);

        $cards = RefereeCard::query()
            ->with(['game.localTeam', 'game.visitorTeam', 'field'])
            ->withCount('entries')
            ->withCount(['entries as total_indirect_red' => fn ($query) => $query->where('yellow_cards', '>=', RefereeCardPlayer::YELLOW_CARDS_FOR_INDIRECT_RED)])
            ->withSum('entries as total_goals', 'goals')
            ->withSum('entries as total_yellow', 'yellow_cards')
            ->withSum('entries as total_direct_red', 'direct_red_cards')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';

                $query->where(function ($query) use ($like) {
                    $query->where('city', 'like', $like)
                        ->orWhereHas('field', fn ($query) => $query->where('name', 'like', $like))
                        ->orWhereHas('game.localTeam', fn ($query) => $query->where('name', 'like', $like))
                        ->orWhereHas('game.visitorTeam', fn ($query) => $query->where('name', 'like', $like));
                });
            })
            ->orderByDesc('match_date')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('referee-cards.index', compact('cards', 'search'));
    }

    /**
     * Formulario para registrar una cédula arbitral.
     */
    public function create(Request $request)
    {
        $match = $request->filled('match_id')
            ? Game::query()->find((int) $request->query('match_id'))
            : null;

        $refereeCard = new RefereeCard([
            'match_id' => $match?->id,
            'field_id' => $match?->field_id,
            'match_date' => $match?->date_time ?? now(),
        ]);

        return view('referee-cards.create', [
            'refereeCard' => $refereeCard,
            ...$this->formOptions($refereeCard),
        ]);
    }

    /**
     * Guardar una cédula arbitral nueva junto con sus jugadores.
     */
    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $players = $data['players'] ?? [];
        unset($data['players']);

        DB::transaction(function () use ($data, $players) {
            $card = RefereeCard::query()->create($data);
            $this->syncPlayers($card, $players, (int) $data['match_id']);
        });

        return redirect()
            ->route('referee-cards.index')
            ->with('success', 'Cédula arbitral registrada correctamente.');
    }

    /**
     * Formulario para editar una cédula arbitral.
     */
    public function edit(RefereeCard $refereeCard)
    {
        $refereeCard->load('entries');

        return view('referee-cards.edit', [
            'refereeCard' => $refereeCard,
            ...$this->formOptions($refereeCard),
        ]);
    }

    /**
     * Actualizar la cédula y reemplazar sus jugadores.
     */
    public function update(Request $request, RefereeCard $refereeCard)
    {
        $data = $this->validatedData($request, $refereeCard);
        $players = $data['players'] ?? [];
        unset($data['players']);

        DB::transaction(function () use ($refereeCard, $data, $players) {
            $refereeCard->update($data);
            $this->syncPlayers($refereeCard, $players, (int) $data['match_id']);
        });

        return redirect()
            ->route('referee-cards.index')
            ->with('success', 'Cédula arbitral actualizada correctamente.');
    }

    /**
     * Eliminar una cédula o varias (ids[] del formulario de acciones).
     */
    public function destroy(Request $request, ?RefereeCard $refereeCard = null)
    {
        $ids = collect($request->input('ids', []))
            ->when($refereeCard?->id, fn ($ids, $id) => $ids->push($id))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecciona al menos una cédula arbitral.');
        }

        $deleted = RefereeCard::query()->whereIn('id', $ids)->delete();

        if ($deleted === 0) {
            return back()->with('error', 'No se eliminó ninguna cédula arbitral.');
        }

        return redirect()
            ->route('referee-cards.index')
            ->with('success', $deleted === 1
                ? 'Cédula arbitral eliminada correctamente.'
                : "{$deleted} cédulas arbitrales eliminadas correctamente.");
    }

    /**
     * Opciones que alimentan los formularios de alta y edición.
     *
     * @return array<string, mixed>
     */
    private function formOptions(?RefereeCard $refereeCard = null): array
    {
        $currentMatchId = $refereeCard?->match_id;

        $matches = Game::query()
            ->with(['localTeam', 'visitorTeam'])
            ->where(function ($query) use ($currentMatchId) {
                $query->whereDoesntHave('refereeCard');

                if ($currentMatchId) {
                    $query->orWhere('id', $currentMatchId);
                }
            })
            ->orderByDesc('date_time')
            ->get();

        $fields = Field::query()->orderBy('name')->get();

        $players = Team::query()
            ->with(['players.person'])
            ->get()
            ->flatMap(function (Team $team) {
                return $team->players->map(function (Player $player) use ($team) {
                    $player->setRelation('team', $team);

                    return $player;
                });
            })
            ->values();

        return [
            'matches' => $matches,
            'fields' => $fields,
            'players' => $players,
            'entries' => $refereeCard?->exists
                ? $refereeCard->entries->keyBy('player_id')
                : collect(),
        ];
    }

    /**
     * Guardar los jugadores marcados en la cédula.
     *
     * @param  array<int|string, array<string, mixed>>  $players
     */
    private function syncPlayers(RefereeCard $card, array $players, int $matchId): void
    {
        $match = Game::query()->find($matchId);

        $validPlayerIds = $match
            ? Player::query()
                ->whereHas('teams', function ($query) use ($match) {
                    $query->whereIn('teams.id', [$match->local_team_id, $match->visitor_team_id]);
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all()
            : [];

        $selectedPlayerIds = collect($players)
            ->filter(fn ($entry) => is_array($entry) && ! empty($entry['selected']))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        if (array_diff($selectedPlayerIds, $validPlayerIds) !== []) {
            throw ValidationException::withMessages([
                'players' => 'Uno de los jugadores no pertenece a los equipos del partido.',
            ]);
        }

        $card->entries()->delete();

        foreach ($selectedPlayerIds as $playerId) {
            $entry = $players[$playerId] ?? [];
            $yellowCards = (int) ($entry['yellow_cards'] ?? 0);

            $card->entries()->create([
                'player_id' => $playerId,
                'goals' => (int) ($entry['goals'] ?? 0),
                'yellow_cards' => $yellowCards,
                'direct_red_cards' => (int) ($entry['direct_red_cards'] ?? 0),
                // Dos amarillas expulsan: la roja indirecta no se captura, se deduce.
                'indirect_red_cards' => RefereeCardPlayer::indirectRedFromYellowCards($yellowCards),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?RefereeCard $refereeCard = null): array
    {
        return $request->validate([
            'match_id' => [
                'required',
                'integer',
                'exists:matches,id',
                Rule::unique('referee_cards', 'match_id')->ignore($refereeCard?->id),
            ],
            'field_id' => ['required', 'integer', 'exists:fields,id'],
            'city' => ['required', 'string', 'max:100'],
            'match_date' => ['required', 'date'],
            'observations' => ['nullable', 'string', 'max:2000'],
            'players' => ['nullable', 'array'],
            'players.*.selected' => ['nullable', 'boolean'],
            'players.*.goals' => ['nullable', 'integer', 'min:0', 'max:99'],
            'players.*.yellow_cards' => ['nullable', 'integer', 'min:0', 'max:2'],
            'players.*.direct_red_cards' => ['nullable', 'integer', 'min:0', 'max:1'],
        ], [
            'match_id.required' => 'El partido es obligatorio.',
            'match_id.exists' => 'El partido seleccionado no es válido.',
            'match_id.unique' => 'Ese partido ya tiene una cédula arbitral registrada.',
            'field_id.required' => 'La cancha es obligatoria.',
            'field_id.exists' => 'La cancha seleccionada no es válida.',
            'city.required' => 'La ciudad es obligatoria.',
            'city.max' => 'La ciudad no puede superar 100 caracteres.',
            'match_date.required' => 'La fecha es obligatoria.',
            'match_date.date' => 'La fecha no es válida.',
            'observations.max' => 'La información general no puede superar 2000 caracteres.',
            'players.array' => 'Los jugadores no tienen un formato válido.',
            'players.*.goals.integer' => 'Los goles de un jugador deben ser un número entero.',
            'players.*.goals.min' => 'Los goles de un jugador no pueden ser negativos.',
            'players.*.goals.max' => 'Los goles de un jugador no pueden superar 99.',
            'players.*.yellow_cards.integer' => 'Las amarillas deben ser un número entero.',
            'players.*.yellow_cards.min' => 'Las amarillas no pueden ser negativas.',
            'players.*.yellow_cards.max' => 'Un jugador no puede acumular más de dos amarillas.',
            'players.*.direct_red_cards.integer' => 'La roja directa debe ser un número entero.',
            'players.*.direct_red_cards.min' => 'La roja directa no puede ser negativa.',
            'players.*.direct_red_cards.max' => 'Un jugador no puede tener más de una roja directa.',
        ]);
    }
}
