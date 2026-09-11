<?php

use App\Enums\TipoPerfil;
use App\Http\Controllers\Auth\CerrarSesionController;
use App\Http\Middleware\VerificarPerfil;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/inicio');

Route::livewire('/inicio', 'pages::inicio')->name('inicio');

Route::middleware('guest')->group(function () {
    Route::livewire('/iniciar-sesion', 'pages::auth.iniciar-sesion')->name('login');
    Route::livewire('/registro', 'pages::auth.registro')->name('registro');
    Route::livewire('/olvidaste-tu-clave', 'pages::auth.solicitar-restablecimiento')->name('password.request');
    Route::livewire('/restablecer-clave/{token}', 'pages::auth.restablecer-clave')->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::post('/cerrar-sesion', CerrarSesionController::class)->name('logout');

    Route::middleware(VerificarPerfil::permitir(TipoPerfil::UsuarioFinal))
        ->prefix('turista')
        ->name('turista.')
        ->group(function () {
            Route::livewire('/', 'pages::turista.panel')->name('panel');
        });

    Route::middleware(VerificarPerfil::permitir(TipoPerfil::TravelGroup))
        ->prefix('travel-group')
        ->name('travel-group.')
        ->group(function () {
            Route::livewire('/', 'pages::travel-group.panel')->name('panel');
            Route::livewire('/zonas', 'pages::travel-group.zonas')->name('zonas');
            Route::livewire('/estaciones', 'pages::travel-group.estaciones')->name('estaciones');
        });

    Route::middleware(VerificarPerfil::permitir(TipoPerfil::AdministradorMtc))
        ->prefix('administracion')
        ->name('administracion.')
        ->group(function () {
            Route::livewire('/', 'pages::administracion.panel')->name('panel');
            Route::livewire('/sincronizacion', 'pages::administracion.sincronizacion')->name('sincronizacion');
            Route::livewire('/usuarios', 'pages::administracion.usuarios')->name('usuarios');
        });
});
