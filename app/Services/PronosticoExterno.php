<?php

namespace App\Services;

/**
 * Pronóstico diario asociado al código externo de una estación.
 */
final readonly class PronosticoExterno
{
    public function __construct(
        public string $codigoEstacion,
        public string $fecha,
        public float $temperaturaMinima,
        public float $temperaturaMaxima,
        public int $probabilidadLluvia,
        public string $descripcion,
    ) {}
}
