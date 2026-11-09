<?php

namespace App\Http\Controllers;

use App\Models\MatchReportPlayer;
use App\Models\PersonData;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PlayerController extends Controller
{
    private const TYPES = ['player', 'coach', 'referee', 'own'];

    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->get('q', '')), 0, 100);

        $players = Player::query()
            ->with(['person', 'teams'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';

                $query->where(function ($query) use ($like) {
                    $query->where('id', 'like', $like)
                        ->orWhereHas('person', function ($person) use ($like) {
                            $person->where('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like);
                        });
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $stats = MatchReportPlayer::query()
            ->whereIn('player_id', $players->pluck('id'))
            ->selectRaw('player_id, COUNT(*) as matches_played, COALESCE(SUM(goals), 0) as total_goals, COALESCE(SUM(yellow_cards), 0) as total_yellow, COALESCE(SUM(red_cards), 0) as total_red')
            ->groupBy('player_id')
            ->get()
            ->keyBy('player_id');

        return view('players.index', compact('players', 'search', 'stats'));
    }

    public function create()
    {
        return view('players.create', [
            'player' => new Player,
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($request, $data) {
            $person = PersonData::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'birth_date' => $data['birth_date'] ?? null,
                'status' => $data['status'],
            ]);

            $player = new Player([
                'person_id' => $person->id,
                'type_id' => $data['type_id'],
            ]);

            if ($request->hasFile('photo')) {
                $player->photo_url = $request->file('photo')->store('players', 'public');
            }

            $player->save();

            if (! empty($data['team_id'])) {
                $player->teams()->attach($data['team_id'], [
                    'captain' => $request->boolean('captain'),
                    'jersey_number' => $data['jersey_number'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('players.index')
            ->with('status', 'Jugador creado.');
    }

    public function edit(Player $player)
    {
        $player->load(['person', 'teams']);

        return view('players.edit', [
            'player' => $player,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Player $player)
    {
        $data = $this->validatedData($request, $player);

        DB::transaction(function () use ($request, $data, $player) {
            $player->person()->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'birth_date' => $data['birth_date'] ?? null,
                'status' => $data['status'],
            ]);

            $player->fill([
                'type_id' => $data['type_id'],
            ]);

            if ($request->hasFile('photo')) {
                if ($player->photo_url) {
                    Storage::disk('public')->delete($player->photo_url);
                }
                $player->photo_url = $request->file('photo')->store('players', 'public');
            }

            $player->save();

            if (! empty($data['team_id'])) {
                $player->teams()->sync([
                    $data['team_id'] => [
                        'captain' => $request->boolean('captain'),
                        'jersey_number' => $data['jersey_number'] ?? null,
                    ],
                ]);
            } else {
                $player->teams()->detach();
            }
        });

        return redirect()
            ->route('players.index')
            ->with('status', 'Jugador actualizado.');
    }

    public function destroy(Request $request, ?Player $player = null)
    {
        $ids = collect($request->input('ids', []))
            ->when($player?->id, fn ($ids, $id) => $ids->push($id))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecciona al menos un jugador.');
        }

        $deleted = 0;
        $blocked = 0;

        foreach (Player::query()->whereIn('id', $ids)->get() as $item) {
            try {
                DB::transaction(function () use ($item) {
                    $personId = $item->person_id;
                    $item->delete();

                    if (! Player::where('person_id', $personId)->exists()) {
                        PersonData::whereKey($personId)->delete();
                    }
                });

                if ($item->photo_url) {
                    Storage::disk('public')->delete($item->photo_url);
                }

                $deleted++;
            } catch (QueryException $e) {
                $blocked++;
            }
        }

        if ($deleted === 0) {
            return back()->with('error', 'No se pudo eliminar: el jugador tiene registros relacionados (por ejemplo, partidos).');
        }

        $message = $deleted === 1 ? 'Jugador eliminado.' : "{$deleted} jugadores eliminados.";

        if ($blocked > 0) {
            $message .= " {$blocked} no se pudieron eliminar por tener registros relacionados.";
        }

        return redirect()->route('players.index')->with('status', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'teams' => Team::query()->orderBy('name')->get(),
            'types' => self::TYPES,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Player $player = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'type_id' => [
                'required',
                Rule::in(self::TYPES),
                Rule::unique('players', 'type_id')
                    ->where('person_id', $player?->person_id)
                    ->ignore($player?->id),
            ],
            'photo' => ['nullable', 'image', 'max:2048'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'jersey_number' => ['nullable', 'integer', 'min:0', 'max:99'],
            'captain' => ['nullable', 'boolean'],
        ], [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'birth_date.date' => 'La fecha de nacimiento no es válida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'status.required' => 'El estatus es obligatorio.',
            'type_id.required' => 'El tipo es obligatorio.',
            'type_id.in' => 'El tipo no es válido.',
            'type_id.unique' => 'Esta persona ya tiene ese tipo registrado.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.max' => 'La imagen no puede superar 2 MB.',
            'team_id.exists' => 'El equipo no es válido.',
            'jersey_number.integer' => 'El número de camiseta debe ser un número.',
            'jersey_number.max' => 'El número de camiseta no puede ser mayor a 99.',
        ]);
    }
}