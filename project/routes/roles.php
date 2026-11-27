<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'module.access'])->group(function () {
    Route::post('roles/process', [RoleController::class, 'process'])->name('roles.process');
    Route::delete('roles/{role?}', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::resource('roles', RoleController::class)->except(['show', 'destroy']);
});

