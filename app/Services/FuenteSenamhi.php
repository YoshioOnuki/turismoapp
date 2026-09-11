<?php

namespace App\Services;

interface FuenteSenamhi
{
    /**
     * @return list<PronosticoExterno>
     */
    public function obtenerPronosticos(): array;
}
