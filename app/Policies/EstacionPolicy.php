<?php

namespace App\Policies;

use App\Enums\TipoPerfil;
use App\Models\Usuario;

class EstacionPolicy
{
    public function consultarAdministracion(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::TravelGroup);
    }

    public function seleccionar(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::UsuarioFinal);
    }

    public function consultarReporte(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::TravelGroup);
    }
}
