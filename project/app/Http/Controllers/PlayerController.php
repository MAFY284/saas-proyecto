<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\PersonData;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PlayerController extends Controller
{
    /**
     * Mostrar el listado de jugadores.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $players = Player::query()
            ->where('type_id', 'player')
            ->with(['person', 'teams'])
            ->withSum('entries as match_goals', 'goals')
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('person', function ($person) use ($search) {
                    $person->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('players.index', compact('players', 'search'));
    }

    /**
     * Mostrar formulario para crear jugadores.
     */
    public function create()
    {
        $teams = Team::orderBy('name')->get();

        $positions = DB::table('positions')
            ->orderBy('name')
            ->get();

        return view('players.create', compact('teams', 'positions'));
    }

    /**
     * Registrar un nuevo jugador.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'status' => 'required|in:active,inactive',
            'team_id' => 'nullable|exists:teams,id',
            'position_id' => 'nullable|exists:positions,id',
            'jersey_number' => 'nullable|integer|min:1|max:999',
            'captain' => 'required|boolean',
            'photo' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif,bmp',
                'max:5120',
            ],
        ], [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'birth_date.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'team_id.exists' => 'El equipo seleccionado no existe.',
            'position_id.exists' => 'La posición seleccionada no existe.',
            'photo.image' => 'El archivo debe ser una imagen válida.',
            'photo.mimes' => 'Formato no permitido. Usa JPG, JPEG, PNG, WEBP, GIF o BMP.',
            'photo.max' => 'La imagen no debe superar los 5 MB.',
        ]);

        $photoPath = null;

        try {
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('players', 'public');
            }

            DB::transaction(function () use ($data, $photoPath) {
                $person = PersonData::create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'birth_date' => $data['birth_date'] ?? null,
                    'status' => $data['status'],
                ]);

                $player = Player::create([
                    'person_id' => $person->id,
                    'type_id' => 'player',
                    'photo_url' => $photoPath,
                ]);

                if (!empty($data['team_id'])) {
                    $player->teams()->attach($data['team_id'], [
                        'jersey_number' => $data['jersey_number'] ?? null,
                        'captain' => (bool) ($data['captain'] ?? false),
                    ]);
                }

                if (!empty($data['position_id'])) {
                    DB::table('player_positions')->insert([
                        'player_id' => $player->id,
                        'position_id' => $data['position_id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            if ($photoPath !== null) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $e;
        }

        return redirect()
            ->route('players.index')
            ->with('success', 'Jugador registrado correctamente.');
    }

    /**
     * Mostrar el formulario para editar al jugador.
     */
    public function edit(Player $player)
    {
        abort_unless($player->type_id === 'player', 404);

        $player->load(['person', 'teams']);

        $teams = Team::orderBy('name')->get();
        $positions = DB::table('positions')->orderBy('name')->get();

        // Mostrar la asociación usada más recientemente, sin eliminar las anteriores.
        $selectedTeamId = DB::table('team_players')
            ->where('player_id', $player->id)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('team_id');

        $selectedTeam = $selectedTeamId
            ? $player->teams->firstWhere('id', $selectedTeamId)
            : null;

        $selectedPositionId = DB::table('player_positions')
            ->where('player_id', $player->id)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('position_id');

        return view('players.edit', compact(
            'player',
            'teams',
            'positions',
            'selectedTeam',
            'selectedPositionId'
        ));
    }

    /**
     * Actualizar un jugador sin borrar asociaciones previas de equipos ni posiciones.
     */
    public function update(Request $request, Player $player)
    {
        abort_unless($player->type_id === 'player', 404);

        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'status' => 'required|in:active,inactive',
            'team_id' => 'nullable|exists:teams,id',
            'position_id' => 'nullable|exists:positions,id',
            'jersey_number' => 'nullable|integer|min:1|max:999',
            'captain' => 'required|boolean',
            'remove_photo' => 'sometimes|boolean',
            'photo' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif,bmp',
                'max:5120',
            ],
        ], [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'birth_date.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'team_id.exists' => 'El equipo seleccionado no existe.',
            'position_id.exists' => 'La posición seleccionada no existe.',
            'photo.image' => 'El archivo debe ser una imagen válida.',
            'photo.mimes' => 'Formato no permitido. Usa JPG, JPEG, PNG, WEBP, GIF o BMP.',
            'photo.max' => 'La imagen no debe superar los 5 MB.',
        ]);

        // El formulario actual no distingue asociaciones históricas de actuales.
        // No permitir desvinculaciones implícitas que podrían borrar información.
        if (empty($data['team_id']) && DB::table('team_players')
            ->where('player_id', $player->id)->exists()) {
            throw ValidationException::withMessages([
                'team_id' => 'No se puede quitar un equipo asociado desde este formulario. Selecciona un equipo para conservar las asociaciones anteriores.',
            ]);
        }

        if (empty($data['position_id']) && DB::table('player_positions')
            ->where('player_id', $player->id)->exists()) {
            throw ValidationException::withMessages([
                'position_id' => 'No se puede quitar una posición asociada desde este formulario. Selecciona una posición para conservar las asociaciones anteriores.',
            ]);
        }

        $oldPhotoPath = $player->photo_url;
        $newPhotoPath = null;
        // Si hay una imagen nueva, se reemplaza la anterior.
        // En caso contrario, se permite quitar la fotografía guardada.
        $removePhoto = $request->boolean('remove_photo') && !$request->hasFile('photo');

        try {
            if ($request->hasFile('photo')) {
                $newPhotoPath = $request->file('photo')->store('players', 'public');
            }

            DB::transaction(function () use ($player, $data, $newPhotoPath, $removePhoto) {
                $person = $player->person;
                abort_if($person === null, 422, 'El jugador no tiene datos personales asociados.');

                $person->update([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'birth_date' => $data['birth_date'] ?? null,
                    'status' => $data['status'],
                ]);

                if ($newPhotoPath !== null) {
                    $player->update(['photo_url' => $newPhotoPath]);
                } elseif ($removePhoto) {
                    $player->update(['photo_url' => null]);
                }

                // Actualizar o registrar el equipo elegido; NO usar sync() ni detach().
                if (!empty($data['team_id'])) {
                    $teamId = (int) $data['team_id'];
                    $teamPlayer = DB::table('team_players')
                        ->where('player_id', $player->id)
                        ->where('team_id', $teamId)
                        ->orderByDesc('id')
                        ->first();

                    $teamAttributes = [
                        'jersey_number' => $data['jersey_number'] ?? null,
                        'captain' => (bool) $data['captain'],
                        'updated_at' => now(),
                    ];

                    if ($teamPlayer) {
                        DB::table('team_players')
                            ->where('id', $teamPlayer->id)
                            ->update($teamAttributes);
                    } else {
                        DB::table('team_players')->insert(array_merge(
                            $teamAttributes,
                            [
                                'player_id' => $player->id,
                                'team_id' => $teamId,
                                'created_at' => now(),
                            ]
                        ));
                    }
                }

                // Conservar las posiciones anteriores y evitar registros duplicados.
                if (!empty($data['position_id'])) {
                    $positionId = (int) $data['position_id'];
                    $playerPosition = DB::table('player_positions')
                        ->where('player_id', $player->id)
                        ->where('position_id', $positionId)
                        ->orderByDesc('id')
                        ->first();

                    if ($playerPosition) {
                        DB::table('player_positions')
                            ->where('id', $playerPosition->id)
                            ->update(['updated_at' => now()]);
                    } else {
                        DB::table('player_positions')->insert([
                            'player_id' => $player->id,
                            'position_id' => $positionId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            // Si la transacción falla, conservar la foto anterior.
            if ($newPhotoPath !== null) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $e;
        }

        // Eliminar el archivo anterior solo DESPUÉS de guardar los cambios.
        if (($newPhotoPath !== null || $removePhoto) && $oldPhotoPath !== null
            && $oldPhotoPath !== $newPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return redirect()
            ->route('players.index')
            ->with('success', 'Jugador actualizado correctamente.');
    }

    /**
     * Eliminar un jugador únicamente si no tiene registros de partidos.
     * No eliminar su PersonData: podría pertenecer a otros registros.
     */
    public function destroy(Player $player)
    {
        abort_unless($player->type_id === 'player', 404);

        $photoPath = $player->photo_url;

        try {
            DB::transaction(function () use ($player) {
                // Bloquear el registro mientras se verifica y elimina.
                $current = Player::query()
                    ->whereKey($player->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_unless($current->type_id === 'player', 404);

                if ($current->entries()->exists()) {
                    throw ValidationException::withMessages([
                        'player' => 'No se puede eliminar este jugador porque tiene registros de partidos.',
                    ]);
                }

                // Borrar únicamente sus vínculos; nunca los equipos o posiciones.
                $current->teams()->detach();
                DB::table('player_positions')
                    ->where('player_id', $current->id)
                    ->delete();

                $current->delete();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Otra relación protegida por claves foráneas impide el borrado.
            if (in_array((string) $e->getCode(), ['23000', '23503'], true)) {
                return redirect()
                    ->route('players.index')
                    ->with('error', 'No se pudo eliminar al jugador porque tiene otros registros asociados.');
            }

            throw $e;
        }

        // El archivo se borra solo después del éxito de la transacción.
        if ($photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        return redirect()
            ->route('players.index')
            ->with('success', 'Jugador eliminado correctamente.');
    }

}
