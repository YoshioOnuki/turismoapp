<?php

namespace App\Policies;

use App\Enums\TipoPerfil;
use App\Models\Usuario;

class CategoriaPolicy
{
    public function administrar(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::AdministradorMtc);
    }

    public function seleccionar(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::UsuarioFinal);
    }
}
