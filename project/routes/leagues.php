<?php

use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('leagues/process', [LeagueController::class, 'process'])->name('leagues.process');
    Route::delete('leagues/{league?}', [LeagueController::class, 'destroy'])->name('leagues.destroy');
    Route::resource('leagues', LeagueController::class)->except(['show', 'destroy']);
});

