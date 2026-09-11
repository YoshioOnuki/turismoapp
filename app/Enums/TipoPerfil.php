<?php

namespace App\Enums;

/**
 * Perfiles del sistema (RF-03). Cada valor coincide con per_codigo en tb_perfil.
 */
enum TipoPerfil: int
{
    case UsuarioFinal = 1;
    case TravelGroup = 2;
    case AdministradorMtc = 3;
}
