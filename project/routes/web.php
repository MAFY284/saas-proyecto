<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('users/process', [UserController::class, 'process'])->name('users.process');
    Route::delete('users/{user?}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::resource('users', UserController::class)->except(['show', 'destroy']);

    Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');
});