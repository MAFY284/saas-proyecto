<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    /**
     * Consultar posiciones registradas.
     */
    public function index()
    {
        $positions = DB::table('positions')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'positions' => $positions
        ]);
    }

    /**
     * Registrar una nueva posición.
     */
    public function store(Request $request)
    {
        if (is_string($request->input('name'))) {
            $request->merge([
                'name' => trim($request->input('name'))
            ]);
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions', 'name')
            ]
        ], [
            'name.required' => 'Debes escribir una posición.',
            'name.unique' => 'Esta posición ya está registrada.',
        ]);

        $id = DB::table('positions')->insertGetId([
            'name' => $data['name'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'Posición registrada correctamente.',
            'position' => [
                'id' => $id,
                'name' => $data['name']
            ]
        ], 201);
    }

    /**
     * Editar una posición existente.
     */
    public function update(Request $request, int $id)
    {
        $position = DB::table('positions')
            ->where('id', $id)
            ->first();

        if (!$position) {
            return response()->json([
                'message' => 'La posición no existe.'
            ], 404);
        }

        if (is_string($request->input('name'))) {
            $request->merge([
                'name' => trim($request->input('name'))
            ]);
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions', 'name')->ignore($id)
            ]
        ], [
            'name.required' => 'Debes escribir una posición.',
            'name.unique' => 'Ya existe otra posición con ese nombre.',
        ]);

        DB::table('positions')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'updated_at' => now()
            ]);

        return response()->json([
            'message' => 'Posición actualizada correctamente.',
            'position' => [
                'id' => $id,
                'name' => $data['name']
            ]
        ]);
    }

    /**
     * Eliminar una posición únicamente si no está en uso.
     */
    public function destroy(int $id)
    {
        return DB::transaction(function () use ($id) {

            $position = DB::table('positions')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$position) {
                return response()->json([
                    'message' => 'La posición no existe.'
                ], 404);
            }

            $inUse = DB::table('player_positions')
                ->where('position_id', $id)
                ->exists();

            if ($inUse) {
                return response()->json([
                    'message' => 'No puedes eliminar esta posición porque tiene jugadores asociados.'
                ], 409);
            }

            DB::table('positions')
                ->where('id', $id)
                ->delete();

            return response()->json([
                'message' => 'Posición eliminada correctamente.'
            ]);
        });
    }
}