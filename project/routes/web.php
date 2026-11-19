<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MatchReportController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RefereeCardController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
    Route::get('access', [AccessController::class, 'index'])->name('access.index');
    Route::put('access/{participantType}', [AccessController::class, 'update'])->name('access.update');
    Route::resource('teams', TeamController::class)->except(['show']);
    Route::delete('players/bulk', [PlayerController::class, 'destroySelected'])
        ->name('players.destroy-selected');
    Route::resource('players', PlayerController::class)
        ->except(['show']);

    Route::delete('matches/{game?}', [MatchController::class, 'destroy'])->name('matches.destroy');
    Route::resource('matches', MatchController::class)
        ->parameters(['matches' => 'game'])
        ->except(['show', 'destroy']);

    Route::delete('match-reports/{matchReport?}', [MatchReportController::class, 'destroy'])->name('match-reports.destroy');
    Route::resource('match-reports', MatchReportController::class)
        ->parameters(['match-reports' => 'matchReport'])
        ->except(['show', 'destroy']);

    Route::delete('referee-cards/{refereeCard?}', [RefereeCardController::class, 'destroy'])->name('referee-cards.destroy');
    Route::resource('referee-cards', RefereeCardController::class)
        ->parameters(['referee-cards' => 'refereeCard'])
        ->except(['show', 'destroy']);

    Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');

    // Administración de posiciones de jugadores
    Route::get('positions', [PositionController::class, 'index'])
        ->name('positions.index');

    Route::post('positions', [PositionController::class, 'store'])
        ->name('positions.store');

    Route::put('positions/{id}', [PositionController::class, 'update'])
        ->name('positions.update');

    Route::delete('positions/{id}', [PositionController::class, 'destroy'])
        ->name('positions.destroy');

    Route::delete('fields/destroy', [FieldController::class, 'destroy'])->name('fields.destroy');
    Route::resource('fields', FieldController::class)->except(['show', 'destroy']);
});

require __DIR__.'/leagues.php';
require __DIR__.'/roles.php';
require __DIR__.'/tournaments.php';
