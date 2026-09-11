<?php

namespace App\Services;

final class FuenteSenamhiSimulada implements FuenteSenamhi
{
    /**
     * @return list<PronosticoExterno>
     */
    public function obtenerPronosticos(): array
    {
        $pronosticos = [];

        /** @var list<array{codigo: string, minima: float, maxima: float, lluvia: int, descripcion: string}> $condiciones */
        $condiciones = [
            ['codigo' => 'WAN', 'minima' => 7.0, 'maxima' => 19.0, 'lluvia' => 30, 'descripcion' => 'Parcialmente nublado'],
            ['codigo' => 'POR', 'minima' => 5.0, 'maxima' => 17.0, 'lluvia' => 35, 'descripcion' => 'Nubes dispersas'],
            ['codigo' => 'OLL', 'minima' => 8.0, 'maxima' => 21.0, 'lluvia' => 25, 'descripcion' => 'Soleado con nubes'],
            ['codigo' => 'MAP', 'minima' => 12.0, 'maxima' => 23.0, 'lluvia' => 55, 'descripcion' => 'Lluvias aisladas'],
            ['codigo' => 'PUN', 'minima' => 3.0, 'maxima' => 16.0, 'lluvia' => 20, 'descripcion' => 'Cielo despejado'],
        ];

        foreach ($condiciones as $condicion) {
            foreach (range(0, 2) as $dias) {
                $pronosticos[] = new PronosticoExterno(
                    codigoEstacion: $condicion['codigo'],
                    fecha: today()->addDays($dias)->toDateString(),
                    temperaturaMinima: $condicion['minima'] + $dias,
                    temperaturaMaxima: $condicion['maxima'] + $dias,
                    probabilidadLluvia: $condicion['lluvia'] + ($dias * 5),
                    descripcion: $condicion['descripcion'],
                );
            }
        }

        return $pronosticos;
    }
}
