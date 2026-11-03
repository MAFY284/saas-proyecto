<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\MatchReport;
use App\Models\Player;
use App\Models\Referee;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MatchReportController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->get('q', '')), 0, 100);
        $status = (string) $request->get('status', '');

        if (! array_key_exists($status, MatchReport::STATUSES)) {
            $status = '';
        }

        $reports = MatchReport::query()
            ->with(['game.homeTeam', 'game.awayTeam', 'referee', 'venue'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';

                $query->where(function ($query) use ($like) {
                    $query->whereHas('game.homeTeam', fn ($query) => $query->where('name', 'like', $like))
                        ->orWhereHas('game.awayTeam', fn ($query) => $query->where('name', 'like', $like))
                        ->orWhereHas('referee', function ($query) use ($like) {
                            $query->where('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like);
                        })
                        ->orWhereHas('venue', fn ($query) => $query->where('name', 'like', $like))
                        ->orWhere('match_date', 'like', $like);
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('match_date')
            ->paginate(8)
            ->withQueryString();

        return view('match-reports.index', [
            'reports' => $reports,
            'search' => $search,
            'status' => $status,
            'statuses' => MatchReport::STATUSES,
        ]);
    }

    public function create(Request $request)
    {
        $match = $request->filled('match_id')
            ? Game::query()->find((int) $request->query('match_id'))
            : null;

        $matchReport = new MatchReport([
            'match_id' => $match?->id,
            'referee_id' => $match?->referee_id,
            'venue_id' => $match?->venue_id,
            'match_date' => $match?->match_date ?? now(),
            'status' => 'completed',
        ]);

        return view('match-reports.create', [
            'matchReport' => $matchReport,
            ...$this->formOptions($matchReport),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $players = $data['players'] ?? [];
        unset($data['players']);

        DB::transaction(function () use ($data, $players) {
            $report = MatchReport::query()->create($data);
            $this->syncPlayers($report, $players, (int) $data['match_id']);
        });

        return redirect()
            ->route('match-reports.index')
            ->with('status', 'Acta creada.');
    }

    public function edit(MatchReport $matchReport)
    {
        $matchReport->load('entries');

        return view('match-reports.edit', [
            'matchReport' => $matchReport,
            ...$this->formOptions($matchReport),
        ]);
    }

    public function update(Request $request, MatchReport $matchReport)
    {
        $data = $this->validatedData($request, $matchReport);
        $players = $data['players'] ?? [];
        unset($data['players']);

        DB::transaction(function () use ($matchReport, $data, $players) {
            $matchReport->update($data);
            $this->syncPlayers($matchReport, $players, (int) $data['match_id']);
        });

        return redirect()
            ->route('match-reports.index')
            ->with('status', 'Acta actualizada.');
    }

    public function destroy(Request $request, ?MatchReport $matchReport = null)
    {
        $ids = collect($request->input('ids', []))
            ->when($matchReport?->id, fn ($ids, $id) => $ids->push($id))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecciona al menos un acta.');
        }

        $deleted = MatchReport::query()->whereIn('id', $ids)->delete();

        if ($deleted === 0) {
            return back()->with('error', 'No se eliminó ningún acta.');
        }

        return redirect()
            ->route('match-reports.index')
            ->with('status', $deleted === 1 ? 'Acta eliminada.' : "{$deleted} actas eliminadas.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?MatchReport $matchReport = null): array
    {
        $currentMatchId = $matchReport?->match_id;

        $matches = Game::query()
            ->with(['homeTeam', 'awayTeam'])
            ->where(function ($query) use ($currentMatchId) {
                $query->whereDoesntHave('report');

                if ($currentMatchId) {
                    $query->orWhere('id', $currentMatchId);
                }
            })
            ->orderByDesc('match_date')
            ->get();

        return [
            'matches' => $matches,
            'referees' => Referee::query()->orderBy('last_name')->orderBy('first_name')->get(),
            'venues' => Venue::query()->orderBy('name')->get(),
            'players' => Player::query()->with('team')->orderBy('team_id')->orderBy('last_name')->get(),
            'entries' => $matchReport?->exists
                ? $matchReport->entries->keyBy('player_id')
                : collect(),
        ];
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $players
     */
    private function syncPlayers(MatchReport $report, array $players, int $matchId): void
    {
        $match = Game::query()->find($matchId);

        if (! $match) {
            return;
        }

        $validPlayerIds = Player::query()
            ->whereIn('team_id', [$match->home_team_id, $match->away_team_id])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $selectedPlayerIds = collect($players)
            ->filter(fn ($entry) => ! empty($entry['selected']))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        if (array_diff($selectedPlayerIds, $validPlayerIds) !== []) {
            throw ValidationException::withMessages([
                'players' => 'Uno de los jugadores no pertenece a los equipos del partido.',
            ]);
        }

        $report->entries()->delete();

        foreach ($selectedPlayerIds as $playerId) {
            $player = Player::query()->find($playerId);

            if (! $player) {
                continue;
            }

            $entry = $players[$playerId] ?? [];

            $report->entries()->create([
                'player_id' => $player->id,
                'team_id' => $player->team_id,
                'is_starter' => ! empty($entry['is_starter']),
                'goals' => (int) ($entry['goals'] ?? 0),
                'yellow_cards' => (int) ($entry['yellow_cards'] ?? 0),
                'red_cards' => (int) ($entry['red_cards'] ?? 0),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?MatchReport $matchReport = null): array
    {
        return $request->validate([
            'match_id' => [
                'required',
                'integer',
                'exists:matches,id',
                Rule::unique('match_reports', 'match_id')->ignore($matchReport?->id),
            ],
            'referee_id' => ['nullable', 'integer', 'exists:referees,id'],
            'venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'match_date' => ['required', 'date'],
            'home_score' => ['nullable', 'integer', 'min:0', 'max:999'],
            'away_score' => ['nullable', 'integer', 'min:0', 'max:999'],
            'incidents' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(MatchReport::STATUSES))],
            'players' => ['nullable', 'array'],
            'players.*.selected' => ['nullable', 'boolean'],
            'players.*.is_starter' => ['nullable', 'boolean'],
            'players.*.goals' => ['nullable', 'integer', 'min:0', 'max:99'],
            'players.*.yellow_cards' => ['nullable', 'integer', 'min:0', 'max:99'],
            'players.*.red_cards' => ['nullable', 'integer', 'min:0', 'max:99'],
        ], [
            'match_id.required' => 'El partido es obligatorio.',
            'match_id.exists' => 'El partido no es válido.',
            'match_id.unique' => 'Ese partido ya tiene un acta registrada.',
            'referee_id.exists' => 'El árbitro no es válido.',
            'venue_id.exists' => 'La sede no es válida.',
            'match_date.required' => 'La fecha del acta es obligatoria.',
            'match_date.date' => 'La fecha del acta no es válida.',
            'home_score.integer' => 'Los goles del local deben ser un número entero.',
            'home_score.min' => 'Los goles del local no pueden ser negativos.',
            'away_score.integer' => 'Los goles del visitante deben ser un número entero.',
            'away_score.min' => 'Los goles del visitante no pueden ser negativos.',
            'incidents.max' => 'Las incidencias no pueden superar 5000 caracteres.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado no es válido.',
            'players.array' => 'Los jugadores no tienen un formato válido.',
            'players.*.goals.integer' => 'Los goles de un jugador deben ser un número entero.',
            'players.*.goals.min' => 'Los goles de un jugador no pueden ser negativos.',
            'players.*.yellow_cards.integer' => 'Las amarillas deben ser un número entero.',
            'players.*.yellow_cards.min' => 'Las amarillas no pueden ser negativas.',
            'players.*.red_cards.integer' => 'Las rojas deben ser un número entero.',
            'players.*.red_cards.min' => 'Las rojas no pueden ser negativas.',
        ]);
    }
}
