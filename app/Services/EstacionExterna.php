<?php

namespace App\Services;

/**
 * Datos de una estación tal como los entrega la fuente ferroviaria.
 */
final readonly class EstacionExterna
{
    public function __construct(
        public string $codigoExterno,
        public string $nombre,
        public float $latitud,
        public float $longitud,
    ) {}
}
