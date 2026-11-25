<?php

use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // 1. PRIMERO la ruta "bulk" para eliminar varios
    Route::delete('tournaments/bulk', [TournamentController::class, 'destroy'])
        ->name('tournaments.destroy-selected');

    // 2. DESPUÉS la ruta resource (que agrupa index, create, store, edit, update, destroy)
    Route::resource('tournaments', TournamentController::class)
        ->except(['show']); 
});