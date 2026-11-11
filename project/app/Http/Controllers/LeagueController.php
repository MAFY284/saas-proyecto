<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\League;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LeagueController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->get('q', '')), 0, 100);

        $leagues = League::query()
            ->with(['country', 'currency'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $dateLike = '%'.str_replace(['/', '.'], '-', addcslashes($search, '%_\\')).'%';

                $query->where(function ($query) use ($like, $dateLike) {
                    $query->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhere('short_name', 'like', $like)
                        ->orWhere('contact_email', 'like', $like)
                        ->orWhere('contact_phone', 'like', $like)
                        ->orWhere('id', 'like', $like)
                        ->orWhere('created_at', 'like', $like)
                        ->orWhere('created_at', 'like', $dateLike);
                });
            })
            ->latest('id')
            ->paginate(8)
            ->withQueryString();

        return view('leagues.index', compact('leagues', 'search'));
    }

    public function create()
    {
        return view('leagues.create', [
            'league' => new League([
                'time_zone' => 'UTC',
                'is_active' => true,
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $league = new League;
        $this->fillLeague($league, $data);

        if ($request->hasFile('logo')) {
            $league->updateLogo($request->file('logo'));
        }

        $league->save();

        return redirect()
            ->route('leagues.index')
            ->with('status', 'Liga creada.');
    }

    public function edit(League $league)
    {
        return view('leagues.edit', [
            'league' => $league,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, League $league)
    {
        $data = $this->validatedData($request, $league);

        $this->fillLeague($league, $data);

        if ($request->hasFile('logo')) {
            $league->updateLogo($request->file('logo'));
        }

        $league->save();

        return redirect()
            ->route('leagues.index')
            ->with('status', 'Liga actualizada.');
    }

    public function destroy(Request $request, ?League $league = null)
    {
        $ids = collect($request->input('ids', []))
            ->when($league?->id, fn ($ids, $id) => $ids->push($id))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecciona al menos una liga.');
        }

        $leagues = League::query()
            ->whereIn('id', $ids)
            ->get();

        foreach ($leagues as $deletedLeague) {
            $deletedLeague->deleteLogoFile();
        }

        $deleted = League::query()
            ->whereIn('id', $leagues->modelKeys())
            ->delete();

        if ($deleted === 0) {
            return back()->with('error', 'No se eliminó ninguna liga.');
        }

        return redirect()
            ->route('leagues.index')
            ->with('status', $deleted === 1 ? 'Liga eliminada.' : "{$deleted} ligas eliminadas.");
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:leagues,id'],
        ], [
            'ids.required' => 'Selecciona al menos una liga.',
            'ids.min' => 'Selecciona al menos una liga.',
        ]);

        $count = count($data['ids']);

        return back()->with(
            'status',
            $count === 1
                ? 'Proceso ejecutado para 1 liga.'
                : "Proceso ejecutado para {$count} ligas."
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'countries' => Country::query()->orderBy('name')->get(),
            'currencies' => Currency::query()->orderBy('name')->get(),
            'timeZones' => timezone_identifiers_list(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function fillLeague(League $league, array $data): void
    {
        $league->fill([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'short_name' => $data['short_name'] ?? null,
            'description' => $data['description'] ?? null,
            'country_id' => $data['country_id'],
            'currency_id' => $data['currency_id'],
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'time_zone' => $data['time_zone'],
            'is_active' => $data['is_active'],
        ]);

        if (! empty($data['logo_url']) && ! request()->hasFile('logo')) {
            $league->logo_url = $data['logo_url'];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?League $league = null): array
    {
        $slug = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('name', ''));

        $request->merge(['slug' => $slug]);

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leagues', 'name')->ignore($league?->id),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leagues', 'slug')->ignore($league?->id),
            ],
            'short_name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'time_zone' => ['required', 'string', 'max:100', Rule::in(timezone_identifiers_list())],
            'is_active' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'El nombre de la liga es obligatorio.',
            'name.unique' => 'Ya existe una liga con este nombre.',
            'name.max' => 'El nombre no puede superar 255 caracteres.',
            'slug.required' => 'El slug es obligatorio.',
            'slug.unique' => 'Este slug ya está en uso.',
            'slug.max' => 'El slug no puede superar 255 caracteres.',
            'short_name.max' => 'El nombre corto no puede superar 100 caracteres.',
            'country_id.required' => 'El país es obligatorio.',
            'country_id.exists' => 'El país no es válido.',
            'currency_id.required' => 'La moneda es obligatoria.',
            'currency_id.exists' => 'La moneda no es válida.',
            'contact_email.email' => 'El email de contacto no es válido.',
            'contact_phone.max' => 'El teléfono no puede superar 50 caracteres.',
            'website_url.url' => 'El sitio web debe ser una URL válida.',
            'time_zone.required' => 'La zona horaria es obligatoria.',
            'time_zone.in' => 'La zona horaria no es válida.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.max' => 'El logo no puede superar 2 MB.',
        ]);
    }
}
