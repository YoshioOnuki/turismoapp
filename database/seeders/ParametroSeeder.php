<?php

namespace Database\Seeders;

use App\Models\Parametro;
use Illuminate\Database\Seeder;

class ParametroSeeder extends Seeder
{
    /**
     * Parámetros generales con sus valores iniciales (RF-20).
     */
    public function run(): void
    {
        $parametros = [
            [
                'par_clave' => 'distancia_maxima_caminable',
                'par_valor' => '3000',
                'par_descripcion' => 'Distancia máxima a pie desde la estación, solo ida, en metros',
            ],
            [
                'par_clave' => 'frecuencia_sincronizacion',
                'par_valor' => '24',
                'par_descripcion' => 'Horas entre sincronizaciones automáticas con PeruRail y SENAMHI',
            ],
            [
                'par_clave' => 'velocidad_caminata',
                'par_valor' => '4',
                'par_descripcion' => 'Velocidad promedio de caminata en km/h para estimar el tiempo de la ruta',
            ],
        ];

        foreach ($parametros as $parametro) {
            Parametro::firstOrCreate(['par_clave' => $parametro['par_clave']], $parametro);
        }
    }
}
