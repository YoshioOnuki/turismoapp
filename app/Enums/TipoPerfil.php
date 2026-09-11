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

    /**
     * Nombre visible del perfil; es el mismo que per_nombre en tb_perfil.
     */
    public function etiqueta(): string
    {
        return match ($this) {
            self::UsuarioFinal => 'Usuario final',
            self::TravelGroup => 'Travel Group Perú',
            self::AdministradorMtc => 'Administrador MTC',
        };
    }

    /**
     * Nombre de la ruta del panel donde el perfil ve sus módulos.
     */
    public function rutaPanel(): string
    {
        return match ($this) {
            self::UsuarioFinal => 'turista.panel',
            self::TravelGroup => 'travel-group.panel',
            self::AdministradorMtc => 'administracion.panel',
        };
    }
}
