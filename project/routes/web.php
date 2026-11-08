<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MatchReportController;
use App\Http\Controllers\PlayerController;
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

Route::redirect('/dashboard', '/');

Route::middleware('auth')->group(function () {
    Route::post('users/process', [UserController::class, 'process'])->name('users.process');
    Route::delete('users/{user?}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::resource('users', UserController::class)->except(['show', 'destroy']);

    Route::delete('matches/{game?}', [MatchController::class, 'destroy'])->name('matches.destroy');
    Route::resource('matches', MatchController::class)
        ->parameters(['matches' => 'game'])
        ->except(['show', 'destroy']);

    Route::delete('match-reports/{matchReport?}', [MatchReportController::class, 'destroy'])->name('match-reports.destroy');
    Route::resource('match-reports', MatchReportController::class)
        ->parameters(['match-reports' => 'matchReport'])
        ->except(['show', 'destroy']);

    Route::delete('players/{player?}', [PlayerController::class, 'destroy'])->name('players.destroy');
    Route::resource('players', PlayerController::class)->except(['show', 'destroy']);

    Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');
});