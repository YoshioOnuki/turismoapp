<?php

namespace App\Enums;

/**
 * Nivel de dificultad de la ruta peatonal hacia una zona turística (RF-08).
 * Cada valor coincide con dif_codigo en tb_dificultad.
 */
enum Dificultad: int
{
    case Baja = 1;
    case Media = 2;
    case Alta = 3;

    /**
     * Nombre visible de la dificultad; es el mismo que dif_nombre en tb_dificultad.
     */
    public function etiqueta(): string
    {
        return match ($this) {
            self::Baja => 'Baja',
            self::Media => 'Media',
            self::Alta => 'Alta',
        };
    }
}
