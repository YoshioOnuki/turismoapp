<?php

namespace App\Services;

/**
 * Datos de un servicio ferroviario entre dos estaciones externas.
 */
final readonly class HorarioExterno
{
    public function __construct(
        public string $codigoExterno,
        public string $codigoEstacionOrigen,
        public string $codigoEstacionDestino,
        public string $servicio,
        public string $horaSalida,
        public string $horaLlegada,
        public float $precio,
    ) {}
}
