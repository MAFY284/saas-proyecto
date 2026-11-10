<?php

use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::delete('tournaments/{tournament?}', [TournamentController::class, 'destroy'])->name('tournaments.destroy');
    Route::resource('tournaments', TournamentController::class)->except(['show', 'destroy']);
});