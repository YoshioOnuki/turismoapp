<?php

namespace App\Enums;

/**
 * Fuentes externas que integra el sistema; cada sincronización se registra en tb_bitacora (RF-14).
 */
enum FuenteDatos: string
{
    case PeruRail = 'perurail';
    case Senamhi = 'senamhi';
    case TravelGroup = 'travel_group';
}
