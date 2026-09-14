<?php

namespace App\Policies;

use App\Enums\TipoPerfil;
use App\Models\Usuario;

class HorarioPolicy
{
    public function administrar(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::AdministradorMtc);
    }
}
