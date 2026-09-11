<?php

namespace Database\Seeders;

use App\Models\Estacion;
use Illuminate\Database\Seeder;

class EstacionSeeder extends Seeder
{
    /**
     * Estaciones de demostración con coordenadas aproximadas. La sincronización
     * con PeruRail (RF-11) las actualiza por est_codigo_externo.
     */
    public function run(): void
    {
        $estaciones = [
            ['est_codigo_externo' => 'WAN', 'est_nombre' => 'Estación Wanchaq (Cusco)', 'est_latitud' => -13.5236, 'est_longitud' => -71.9669],
            ['est_codigo_externo' => 'POR', 'est_nombre' => 'Estación Poroy', 'est_latitud' => -13.4953, 'est_longitud' => -72.0419],
            ['est_codigo_externo' => 'OLL', 'est_nombre' => 'Estación Ollantaytambo', 'est_latitud' => -13.2626, 'est_longitud' => -72.2654],
            ['est_codigo_externo' => 'MAP', 'est_nombre' => 'Estación Machu Picchu (Aguas Calientes)', 'est_latitud' => -13.1547, 'est_longitud' => -72.5252],
            ['est_codigo_externo' => 'PUN', 'est_nombre' => 'Estación Puno', 'est_latitud' => -15.8395, 'est_longitud' => -70.0233],
        ];

        foreach ($estaciones as $estacion) {
            Estacion::updateOrCreate(['est_codigo_externo' => $estacion['est_codigo_externo']], $estacion);
        }
    }
}
