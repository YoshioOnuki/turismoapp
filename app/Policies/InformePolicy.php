<?php

namespace App\Policies;

use App\Enums\TipoPerfil;
use App\Models\Informe;
use App\Models\Usuario;

class InformePolicy
{
    public function gestionarPropios(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::UsuarioFinal);
    }

    public function consultarUso(Usuario $usuario): bool
    {
        return $usuario->tienePerfil(TipoPerfil::AdministradorMtc);
    }

    public function exportar(Usuario $usuario, Informe $informe): bool
    {
        return $usuario->tienePerfil(TipoPerfil::UsuarioFinal)
            && $informe->inf_usu_codigo === $usuario->getKey();
    }
}
