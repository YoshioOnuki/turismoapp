<?php

namespace App\Enums;

/**
 * Origen de una sincronización: programada (RF-13) o lanzada por el administrador (RF-15).
 * Cada valor coincide con tsi_codigo en tb_tipo_sincronizacion.
 */
enum TipoSincronizacion: int
{
    case Automatica = 1;
    case Manual = 2;

    /**
     * Nombre visible del tipo; es el mismo que tsi_nombre en tb_tipo_sincronizacion.
     */
    public function etiqueta(): string
    {
        return match ($this) {
            self::Automatica => 'Automática',
            self::Manual => 'Manual',
        };
    }
}
