<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\InicioController;
use Illuminate\Support\Facades\Route;

Route::get('/', InicioController::class)->name('inicio');

Route::middleware('guest')->group(function (): void {
    Route::get('/ingresar', [SessionController::class, 'create'])->name('sesion.create');
    Route::post('/ingresar', [SessionController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('sesion.store');

    Route::get('/registro', [RegistrationController::class, 'create'])->name('registro.create');
    Route::post('/registro', [RegistrationController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('registro.store');
});

Route::post('/salir', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('sesion.destroy');

Route::get('/cuenta', [AccountController::class, 'show'])
    ->middleware('auth')
    ->name('cuenta');
