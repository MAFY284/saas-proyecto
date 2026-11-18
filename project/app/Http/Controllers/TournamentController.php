<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\League;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TournamentController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::with('league')->latest()->paginate(10);
        return view('tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        $leagues = League::all();
        $statuses = Tournament::STATUSES;
        return view('tournaments.create', compact('leagues', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'league_id'  => ['required', 'exists:leagues,id'],
            'name'       => ['required', 'string', 'max:255'],
            'season'     => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'format'     => ['nullable', 'string', 'max:50'],
            'status'     => ['required', Rule::in(array_keys(Tournament::STATUSES))],
        ]);

        Tournament::create($validated);

        return redirect()->route('tournaments.index')
                         ->with('success', 'Torneo creado con éxito.');
    }

    public function show(Tournament $tournament)
    {
        $tournament->load(['league', 'games']);
        return view('tournaments.show', compact('tournament'));
    }

    public function edit(Tournament $tournament)
    {
        $leagues = League::all();
        $statuses = Tournament::STATUSES;
        return view('tournaments.edit', compact('tournament', 'leagues', 'statuses'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'league_id'  => ['required', 'exists:leagues,id'],
            'name'       => ['required', 'string', 'max:255'],
            'season'     => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'format'     => ['nullable', 'string', 'max:50'],
            'status'     => ['required', Rule::in(array_keys(Tournament::STATUSES))],
        ]);

        $tournament->update($validated);

        return redirect()->route('tournaments.index')
                         ->with('success', 'Torneo actualizado correctamente.');
    }

    public function destroy(Request $request, Tournament $tournament = null)
    {
        // 1. Si se seleccionaron múltiples torneos con los checkboxes
        if ($request->has('ids') && is_array($request->ids)) {
            Tournament::whereIn('id', $request->ids)->delete();

            return redirect()->route('tournaments.index')
                             ->with('success', 'Torneos seleccionados eliminados correctamente.');
        }

        // 2. Si es una eliminación individual mediante inyección de modelos
        if ($tournament && $tournament->exists) {
            $tournament->delete();

            return redirect()->route('tournaments.index')
                             ->with('success', 'Torneo eliminado correctamente.');
        }

        return redirect()->route('tournaments.index')
                         ->with('error', 'No se encontró ningún torneo para eliminar.');
    }
}