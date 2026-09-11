<?php

namespace App\Enums;

/**
 * Nivel de dificultad de la ruta peatonal hacia una zona turística (RF-08).
 */
enum Dificultad: string
{
    case Baja = 'baja';
    case Media = 'media';
    case Alta = 'alta';
}
