<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Game;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->get('q', '')), 0, 100);
        $tournamentId = (int) $request->get('tournament_id', 0);
        $status = (string) $request->get('status', '');

        if (! array_key_exists($status, Game::STATUSES)) {
            $status = '';
        }

        $games = Game::query()
            ->with(['tournament', 'localTeam', 'visitorTeam', 'field'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';

                $query->where(function ($query) use ($like) {
                    $query->whereHas('localTeam', fn ($query) => $query->where('name', 'like', $like))
                        ->orWhereHas('visitorTeam', fn ($query) => $query->where('name', 'like', $like))
                        ->orWhereHas('tournament', fn ($query) => $query->where('name', 'like', $like))
                        ->orWhereHas('field', fn ($query) => $query->where('name', 'like', $like))
                        ->orWhere('date_time', 'like', $like);
                });
            })
            ->when($tournamentId > 0, fn ($query) => $query->where('tournament_id', $tournamentId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('date_time')
            ->paginate(8)
            ->withQueryString();

        return view('matches.index', [
            'games' => $games,
            'search' => $search,
            'tournamentId' => $tournamentId,
            'status' => $status,
            'tournaments' => Tournament::query()->orderBy('name')->get(),
            'statuses' => Game::STATUSES,
        ]);
    }

    public function create()
    {
        return view('matches.create', [
            'game' => new Game([
                'date_time' => now()->addDay(),
                'status' => 'scheduled',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        Game::query()->create($this->validatedData($request));

        return redirect()
            ->route('matches.index')
            ->with('status', 'Partido creado.');
    }

    public function edit(Game $game)
    {
        return view('matches.edit', [
            'game' => $game,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Game $game)
    {
        $game->update($this->validatedData($request, $game));

        return redirect()
            ->route('matches.index')
            ->with('status', 'Partido actualizado.');
    }

    public function destroy(Request $request, ?Game $game = null)
    {
        $ids = collect($request->input('ids', []))
            ->when($game?->id, fn ($ids, $id) => $ids->push($id))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecciona al menos un partido.');
        }

        $hasReports = Game::query()
            ->whereIn('id', $ids)
            ->whereHas('report')
            ->exists();

        if ($hasReports) {
            return back()->with('error', 'No se pueden eliminar partidos que ya tienen un acta registrada.');
        }

        $deleted = Game::query()->whereIn('id', $ids)->delete();

        if ($deleted === 0) {
            return back()->with('error', 'No se eliminó ningún partido.');
        }

        return redirect()
            ->route('matches.index')
            ->with('status', $deleted === 1 ? 'Partido eliminado.' : "{$deleted} partidos eliminados.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'tournaments' => Tournament::query()->orderBy('name')->get(),
            'teams' => Team::query()->orderBy('name')->get(),
            'fields' => Field::query()->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Game $game = null): array
    {
        $data = $request->validate([
            'tournament_id' => ['required', 'integer', 'exists:tournaments,id'],
            'local_team_id' => ['required', 'integer', 'exists:teams,id'],
            'visitor_team_id' => ['required', 'integer', 'exists:teams,id', 'different:local_team_id'],
            'field_id' => ['required', 'integer', 'exists:fields,id'],
            'date_time' => ['required', 'date'],
            'local_goals' => ['nullable', 'integer', 'min:0', 'max:999'],
            'visitor_goals' => ['nullable', 'integer', 'min:0', 'max:999'],
            'matchday' => ['nullable', 'integer', 'min:1', 'max:999'],
            'status' => ['required', Rule::in(array_keys(Game::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'tournament_id.required' => 'El torneo es obligatorio.',
            'tournament_id.exists' => 'El torneo no es válido.',
            'local_team_id.required' => 'El equipo local es obligatorio.',
            'local_team_id.exists' => 'El equipo local no es válido.',
            'visitor_team_id.required' => 'El equipo visitante es obligatorio.',
            'visitor_team_id.exists' => 'El equipo visitante no es válido.',
            'visitor_team_id.different' => 'El equipo visitante no puede ser el mismo que el local.',
            'field_id.required' => 'La sede es obligatoria.',
            'field_id.exists' => 'La sede no es válida.',
            'date_time.required' => 'La fecha del partido es obligatoria.',
            'date_time.date' => 'La fecha del partido no es válida.',
            'local_goals.integer' => 'Los goles del local deben ser un número entero.',
            'local_goals.min' => 'Los goles del local no pueden ser negativos.',
            'visitor_goals.integer' => 'Los goles del visitante deben ser un número entero.',
            'visitor_goals.min' => 'Los goles del visitante no pueden ser negativos.',
            'matchday.integer' => 'La jornada debe ser un número entero.',
            'matchday.min' => 'La jornada debe ser mayor que cero.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado no es válido.',
            'notes.max' => 'Las notas no pueden superar 2000 caracteres.',
        ]);

        $data['local_goals'] = $data['local_goals'] ?? 0;
        $data['visitor_goals'] = $data['visitor_goals'] ?? 0;

        return $data;
    }
}
