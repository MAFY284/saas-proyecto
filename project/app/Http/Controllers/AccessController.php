<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccessController extends Controller
{
    public function index()
    {
        return view('access.index', [
            'participantTypes' => Role::query()->participantTypes()->with('permissions')->orderBy('name')->get(),
            'accessPermissions' => Permission::query()
                ->where('resource', 'like', 'access:%')
                ->get()
                ->keyBy('resource'),
            'resources' => Permission::RESOURCES,
            'resourceTables' => Permission::RESOURCE_TABLES,
            'actions' => Permission::ACTIONS,
        ]);
    }

    public function update(Request $request, ?string $participantType = null)
    {
        if ($participantType !== null) {
            return $this->updateRole($request, $participantType);
        }

        $input = $request->validate(['matrix' => ['required', 'json']]);
        $request->merge(['permissions' => json_decode($input['matrix'], true)]);
        $data = $request->validate([
            'permissions' => ['present', 'array'],
            'permissions.*' => ['required', 'array:'.implode(',', array_keys(Permission::RESOURCES))],
            'permissions.*.*' => [
                'required',
                'array:'.implode(',', array_keys(Permission::ACTIONS)),
                'required_array_keys:'.implode(',', array_keys(Permission::ACTIONS)),
            ],
            'permissions.*.*.*' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($data) {
            $roles = Role::query()->participantTypes()->whereIn('id', array_keys($data['permissions']))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($data['permissions'] as $id => $resources) {
                $role = $roles->get($id);

                if (! $role) {
                    throw ValidationException::withMessages(['matrix' => 'Un tipo de participante ya no está disponible.']);
                }

                foreach ($resources as $resource => $actions) {
                    $this->savePermissions($role, $resource, $actions);
                }
            }
        });

        return redirect()->route('access.index')->with('status', 'Accesos guardados.');
    }

    private function updateRole(Request $request, string $participantType)
    {
        $roles = Role::query()->participantTypes();
        $role = preg_match('/^role-(\d+)$/', $participantType, $matches)
            ? $roles->findOrFail($matches[1])
            : $roles->whereIn('slug', [$participantType, 'participant:'.$participantType, 'participant_'.$participantType])
                ->firstOrFail();

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array:'.implode(',', array_keys(Permission::ACTIONS))],
            'permissions.*.*' => ['required', 'boolean'],
        ]);

        $submitted = $data['permissions'] ?? [];

        DB::transaction(function () use ($role, $submitted) {
            foreach (array_keys(Permission::RESOURCES) as $key) {
                $this->savePermissions($role, $key, $submitted[$key] ?? []);
            }
        });

        return redirect()->route('access.index')->with(
            'status',
            'Accesos actualizados para '.$role->name.'.'
        );
    }

    private function savePermissions(Role $role, string $resource, array $actions): void
    {
        $flags = [];

        foreach (array_keys(Permission::ACTIONS) as $action) {
            $flags['can_'.$action] = (bool) ($actions[$action] ?? false);
        }

        Permission::query()->updateOrCreate(['resource' => $role->accessKey($resource)], $flags);
    }
}
