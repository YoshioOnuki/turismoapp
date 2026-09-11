<?php

use App\Http\Controllers\Auth\CerrarSesionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/inicio');

Route::livewire('/inicio', 'pages::inicio')->name('inicio');

Route::middleware('guest')->group(function () {
    Route::livewire('/iniciar-sesion', 'pages::auth.iniciar-sesion')->name('login');
    Route::livewire('/registro', 'pages::auth.registro')->name('registro');
});

Route::post('/cerrar-sesion', CerrarSesionController::class)
    ->middleware('auth')
    ->name('logout');
