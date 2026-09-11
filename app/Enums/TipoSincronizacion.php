<?php

namespace App\Enums;

/**
 * Origen de una sincronización: programada (RF-13) o lanzada por el administrador (RF-15).
 */
enum TipoSincronizacion: string
{
    case Automatica = 'automatica';
    case Manual = 'manual';
}
