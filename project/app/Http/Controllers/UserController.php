<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->get('q', '')), 0, 100);

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $dateLike = '%'.str_replace(['/', '.'], '-', addcslashes($search, '%_\\')).'%';

                $query->where(function ($query) use ($like, $dateLike) {
                    $query->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('id', 'like', $like)
                        ->orWhere('created_at', 'like', $like)
                        ->orWhere('created_at', 'like', $dateLike);
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function create()
    {
        return view('create', [
            'user' => new User,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $user = new User;
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        if ($request->hasFile('avatar')) {
            $user->updateAvatar($request->file('avatar'));
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validatedData($request, $user);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        if ($request->hasFile('avatar')) {
            $user->updateAvatar($request->file('avatar'));
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario actualizado.');
    }

    public function destroy(Request $request, ?User $user = null)
    {
        $ids = collect($request->input('ids', []))
            ->when($user?->id, fn ($ids, $id) => $ids->push($id))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecciona al menos un usuario.');
        }

        $users = User::query()
            ->whereIn('id', $ids)
            ->whereKeyNot($request->user()->id)
            ->get();

        foreach ($users as $deletedUser) {
            $deletedUser->deleteAvatarFile();
        }

        $deleted = User::query()
            ->whereIn('id', $users->modelKeys())
            ->delete();

        if ($deleted === 0) {
            return back()->with('error', 'No se eliminó ningún usuario. No puedes eliminar tu propia cuenta.');
        }

        return redirect()
            ->route('users.index')
            ->with('status', $deleted === 1 ? 'Usuario eliminado.' : "{$deleted} usuarios eliminados.");
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ], [
            'ids.required' => 'Selecciona al menos un usuario.',
            'ids.min' => 'Selecciona al menos un usuario.',
        ]);

        $count = count($data['ids']);

        return back()->with(
            'status',
            $count === 1
                ? 'Proceso ejecutado para 1 usuario.'
                : "Proceso ejecutado para {$count} usuarios."
        );
    }

    /**
     * @return array{name: string, email: string, password?: string}
     */
    private function validatedData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email no es válido.',
            'email.unique' => 'El email ya está en uso.',
            'password.required' => 'El password es obligatorio.',
            'password.min' => 'El password debe tener al menos 8 caracteres.',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.max' => 'La imagen no puede superar 2 MB.',
        ]);
    }
}
