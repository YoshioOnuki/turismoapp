<?php

namespace App\Policies;

use App\Enums\TipoPerfil;
use App\Models\Usuario;

class ParametroPolicy
{
    public function administrar(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::AdministradorMtc);
    }
}
