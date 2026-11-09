<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    /**
     * Muestra el listado de equipos.
     */
    public function index()
    {
        $teams = DB::table('teams')
            ->leftJoin('localities', 'teams.locality_id', '=', 'localities.id')
            ->select('teams.*', 'localities.name as locality_name')
            ->latest('teams.created_at')
            ->paginate(10);

        return view('teams.index', compact('teams'));
    }

    /**
     * Muestra el formulario para crear un nuevo equipo.
     */
    public function create()
    {
        $localities = DB::table('localities')->orderBy('name', 'asc')->get();
        return view('teams.create', compact('localities'));
    }

    /**
     * Guarda un equipo recién creado en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:teams,name',
            'locality_id'   => 'required|exists:localities,id',
            'short_name'    => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'logo_url'      => 'nullable|url|max:500',
        ], [
            'name.required'        => 'El nombre del equipo es obligatorio.',
            'name.unique'          => 'Ya existe un equipo registrado con este nombre.',
            'locality_id.required' => 'Debes seleccionar una localidad.',
            'locality_id.exists'   => 'La localidad seleccionada no es válida.',
            'contact_email.email'  => 'El formato del correo electrónico no es válido.',
            'logo_url.url'         => 'El logo debe ser una URL válida.',
        ]);

        Team::create([
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'locality_id'   => $request->locality_id,
            'short_name'    => $request->short_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'logo_url'      => $request->logo_url,
            'is_active'     => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('teams.index')->with('success', 'Equipo creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un equipo existente.
     */
    public function edit(Team $team)
    {
        $localities = DB::table('localities')->orderBy('name', 'asc')->get();
        return view('teams.edit', compact('team', 'localities'));
    }

    /**
     * Actualiza el equipo en la base de datos.
     */
    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:teams,name,' . $team->id,
            'locality_id'   => 'required|exists:localities,id',
            'short_name'    => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'logo_url'      => 'nullable|url|max:500',
        ], [
            'name.required'        => 'El nombre del equipo es obligatorio.',
            'name.unique'          => 'Ya existe un equipo registrado con este nombre.',
            'locality_id.required' => 'Debes seleccionar una localidad.',
            'locality_id.exists'   => 'La localidad seleccionada no es válida.',
            'contact_email.email'  => 'El formato del correo electrónico no es válido.',
            'logo_url.url'         => 'El logo debe ser una URL válida.',
        ]);

        $team->update([
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'locality_id'   => $request->locality_id,
            'short_name'    => $request->short_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'logo_url'      => $request->logo_url,
            'is_active'     => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('teams.index')->with('success', 'Equipo actualizado correctamente.');
    }
}