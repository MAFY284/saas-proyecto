<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    /**
     * Muestra el listado de equipos con soporte para búsqueda y filtrado de estado.
     */
    public function index(Request $request)
    {
        $search = $request->input('q');
        $status = $request->input('status');

        $query = Team::with('locality')->latest();

        // Aplicar filtro de búsqueda si existe
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhereHas('locality', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Aplicar filtro de estado si se seleccionó Activo (1) o Inactivo (0)
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status);
        }

        $teams = $query->paginate(10)->withQueryString();

        return view('teams.index', compact('teams', 'search', 'status'));
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
            'is_active'     => 'required|in:0,1',
            'logo_url'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required'         => 'El nombre del equipo es obligatorio.',
            'name.unique'           => 'Ya existe un equipo registrado con este nombre.',
            'locality_id.required'  => 'Debes seleccionar una localidad.',
            'locality_id.exists'    => 'La localidad seleccionada no es válida.',
            'contact_email.email'   => 'El formato del correo electrónico no es válido.',
            'logo_url.image'        => 'El archivo seleccionado debe ser una imagen.',
            'logo_url.mimes'        => 'El logo debe ser un archivo de tipo: jpeg, png, jpg, gif.',
            'logo_url.max'          => 'La imagen no debe pesar más de 2MB.',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo_url')) {
            $logoPath = $request->file('logo_url')->store('images/teams', 'public');
        }

        Team::create([
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'locality_id'   => $request->locality_id,
            'short_name'    => $request->short_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'logo_url'      => $logoPath,
            'is_active'     => (int) $request->is_active,
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
            'is_active'     => 'required|in:0,1',
            'logo_url'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required'         => 'El nombre del equipo es obligatorio.',
            'name.unique'           => 'Ya existe un equipo registrado con este nombre.',
            'locality_id.required'  => 'Debes seleccionar una localidad.',
            'locality_id.exists'    => 'La localidad seleccionada no es válida.',
            'contact_email.email'   => 'El formato del correo electrónico no es válido.',
            'logo_url.image'        => 'El archivo seleccionado debe ser una imagen.',
            'logo_url.mimes'        => 'El logo debe ser un archivo de tipo: jpeg, png, jpg, gif.',
            'logo_url.max'          => 'La imagen no debe pesar más de 2MB.',
        ]);

        $logoPath = $team->logo_url;
        if ($request->hasFile('logo_url')) {
            if ($team->logo_url && Storage::disk('public')->exists($team->logo_url)) {
                Storage::disk('public')->delete($team->logo_url);
            }
            $logoPath = $request->file('logo_url')->store('images/teams', 'public');
        }

        $team->update([
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'locality_id'   => $request->locality_id,
            'short_name'    => $request->short_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'logo_url'      => $logoPath,
            'is_active'     => (int) $request->is_active,
        ]);

        return redirect()->route('teams.index')->with('success', 'Equipo actualizado correctamente.');
    }
}