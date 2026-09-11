<?php

namespace App\Policies;

use App\Enums\TipoPerfil;
use App\Models\Usuario;

class ZonaTuristicaPolicy
{
    public function administrar(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::TravelGroup);
    }

    public function consultar(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::UsuarioFinal);
    }
}
