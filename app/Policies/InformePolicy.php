<?php

namespace App\Policies;

use App\Enums\TipoPerfil;
use App\Models\Usuario;

class InformePolicy
{
    public function gestionarPropios(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::UsuarioFinal);
    }
}
