<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FieldController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->get('q', '')), 0, 100);
        $status = (string) $request->get('status', '');

        if (! array_key_exists($status, Field::STATUSES)) {
            $status = '';
        }

        $fields = Field::query()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where('name', 'like', $like)
                      ->orWhere('address', 'like', $like);
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('fields.index', [
            'fields' => $fields,
            'search' => $search,
            'status' => $status,
            'statuses' => Field::STATUSES,
        ]);
    }

    public function create()
    {
        return view('fields.create', [
            'field' => new Field(['status' => 'available']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        
        $addressId = \Illuminate\Support\Facades\DB::table('addresses')->value('id');

        // Medida de seguridad.
        if (!$addressId) {
            return back()->with('error', 'No hay direcciones en la base de datos. Ejecuta los seeders primero.');
        }

        $data['address_id'] = $addressId;

        Field::query()->create($data);

        return redirect()
            ->route('fields.index')
            ->with('status', 'Sede creada.');
    }

    public function edit(Field $field)
    {
        return view('fields.edit', [
            'field' => $field,
        ]);
    }

    public function update(Request $request, Field $field)
    {
        $data = $this->validatedData($request);
        $field->update($data);

        return redirect()
            ->route('fields.index')
            ->with('status', 'Sede actualizada.');
    }

    public function destroy(Request $request, ?Field $field = null)
    {
        $ids = collect($request->input('ids', []))
            ->when($field?->id, fn ($ids, $id) => $ids->push($id))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecciona al menos una sede.');
        }

        $hasGames = Field::query()
            ->whereIn('id', $ids)
            ->whereHas('games')
            ->exists();

        if ($hasGames) {
            return back()->with('error', 'No se pueden eliminar sedes que ya tienen partidos programados.');
        }

        $deleted = Field::query()->whereIn('id', $ids)->delete();

        if ($deleted === 0) {
            return back()->with('error', 'No se eliminó ninguna sede.');
        }

        return redirect()
            ->route('fields.index')
            ->with('status', $deleted === 1 ? 'Sede eliminada.' : "{$deleted} sedes eliminadas.");
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'surface_type' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'operating_hours' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Field::STATUSES))],
        ], [
            'name.required' => 'El nombre de la sede es obligatorio.',
            'name.max' => 'El nombre no puede superar 100 caracteres.',
            'address.max' => 'La dirección no puede superar 255 caracteres.',
            'surface_type.max' => 'El tipo de superficie no puede superar 50 caracteres.',
            'capacity.integer' => 'La capacidad debe ser un número entero.',
            'capacity.min' => 'La capacidad no puede ser negativa.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado no es válido.',
        ]);
    }
}