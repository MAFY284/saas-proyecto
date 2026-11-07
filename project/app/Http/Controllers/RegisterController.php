<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create()
    {
        return view('register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $parts = preg_split('/\s+/', trim($data['name']), 2) ?: [];
        $data['first_name'] = ($parts[0] ?? '') !== '' ? $parts[0] : $data['name'];
        $data['last_name'] = ($parts[1] ?? '') !== '' ? $parts[1] : $data['first_name'];

        $language = Language::query()->firstOrCreate(
            ['iso_code' => 'es'],
            ['name' => 'Español', 'native_name' => 'Español'],
        );

        $data['language_id'] = $language->id;

        $user = User::create($data);

        Auth::login($user);

        return redirect()->route('home');
    }
}
