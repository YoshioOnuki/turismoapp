<?php

namespace App\Services;

interface FuentePeruRail
{
    /**
     * @return list<EstacionExterna>
     */
    public function obtenerEstaciones(): array;

    /**
     * @return list<HorarioExterno>
     */
    public function obtenerHorarios(): array;
}
