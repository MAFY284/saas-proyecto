<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    public function index()
    {
        $participantTypes = $this->participantTypes();
        $prefix = 'access:';

        return view('access.index', [
            'participantTypes' => $participantTypes,
            'accessPermissions' => Permission::query()
                ->where('resource', 'like', $prefix.'%')
                ->get()
                ->keyBy('resource'),
            'resources' => Permission::RESOURCES,
            'resourceTables' => Permission::RESOURCE_TABLES,
        ]);
    }

    public function update(Request $request, string $participantType)
    {
        abort_unless(array_key_exists($participantType, $this->participantTypes()), 404);

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*.read' => ['nullable', 'boolean'],
            'permissions.*.write' => ['nullable', 'boolean'],
        ]);

        $submitted = $data['permissions'] ?? [];

        foreach (Permission::RESOURCES as $key => $label) {
            $read = ! empty($submitted[$key]['read']);
            $write = ! empty($submitted[$key]['write']);

            Permission::updateOrCreate(
                ['resource' => $this->permissionResource($participantType, $key)],
                [
                    'can_read' => $read,
                    'can_write' => $write,
                    'can_create' => $write,
                    'can_delete' => $write,
                ]
            );
        }

        $role = \App\Models\Role::query()
            ->where('slug', 'participant:'.$participantType)
            ->orWhere('slug', $participantType)
            ->first();

        if ($role) {
            $permissionIds = Permission::query()
                ->where('resource', 'like', "access:{$participantType}:%")
                ->pluck('id')
                ->all();
            $role->permissions()->sync($permissionIds);
        }

        return redirect()->route('access.index')->with(
            'status',
            'Accesos actualizados para '.$this->participantTypes()[$participantType].'.'
        );
    }

    /** @return array<string, string> */
    private function participantTypes(): array
    {
        return [
            'jugador' => 'Jugador',
            'arbitro' => 'Árbitro',
            'entrenador' => 'Entrenador',
            'delegado' => 'Delegado',
            'directivo' => 'Directivo',
        ];
    }

    private function permissionResource(string $participantType, string $resource): string
    {
        return "access:{$participantType}:{$resource}";
    }
}
