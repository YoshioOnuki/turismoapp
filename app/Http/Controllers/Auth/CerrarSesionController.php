<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AutenticacionService;
use Illuminate\Http\RedirectResponse;

class CerrarSesionController extends Controller
{
    /**
     * Cierra la sesión del usuario (RF-02) y lo lleva a la página de inicio.
     */
    public function __invoke(AutenticacionService $autenticacion): RedirectResponse
    {
        $autenticacion->cerrarSesion();

        return redirect()->route('inicio');
    }
}
