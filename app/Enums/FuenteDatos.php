<?php

namespace App\Enums;

/**
 * Fuentes externas que integra el sistema; cada sincronización se registra en tb_bitacora (RF-14).
 * Cada valor coincide con fue_codigo en tb_fuente.
 */
enum FuenteDatos: int
{
    case PeruRail = 1;
    case Senamhi = 2;
    case TravelGroup = 3;

    /**
     * Nombre visible de la fuente; es el mismo que fue_nombre en tb_fuente.
     */
    public function etiqueta(): string
    {
        return match ($this) {
            self::PeruRail => 'PeruRail',
            self::Senamhi => 'SENAMHI',
            self::TravelGroup => 'Travel Group Perú',
        };
    }
}
