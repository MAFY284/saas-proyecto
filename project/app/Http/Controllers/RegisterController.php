<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create()
    {
        return view('register', [
            'userTypes' => UserType::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user_type_id' => ['nullable', 'integer', 'exists:user_types,id'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $parts = preg_split('/\s+/', trim($data['name']), 2) ?: [];
        $data['first_name'] = ($parts[0] ?? '') !== '' ? $parts[0] : $data['name'];
        $data['last_name'] = ($parts[1] ?? '') !== '' ? $parts[1] : $data['first_name'];

        $user = User::create($data);

        Auth::login($user);

        return redirect()->route('home');
    }
}
