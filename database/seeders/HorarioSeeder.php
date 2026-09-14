<?php

namespace Database\Seeders;

use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Servicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class HorarioSeeder extends Seeder
{
    /**
     * Horarios de demostración entre estaciones, con precios aproximados en soles.
     * La sincronización con PeruRail (RF-11) los actualiza por hor_codigo_externo.
     */
    public function run(): void
    {
        $estaciones = Estacion::pluck('est_codigo', 'est_codigo_externo');

        foreach ($this->horarios() as $horario) {
            Horario::updateOrCreate(
                ['hor_codigo_externo' => $horario['hor_codigo_externo']],
                [
                    ...Arr::except($horario, ['origen', 'destino', 'servicio']),
                    'hor_est_codigo_origen' => $estaciones[$horario['origen']],
                    'hor_est_codigo_destino' => $estaciones[$horario['destino']],
                    'hor_ser_codigo' => Servicio::query()->firstOrCreate(['ser_nombre' => $horario['servicio']])->getKey(),
                ],
            );
        }
    }

    /**
     * @return list<array{hor_codigo_externo: string, origen: string, destino: string, servicio: string, hor_hora_salida: string, hor_hora_llegada: string, hor_precio: float}>
     */
    private function horarios(): array
    {
        return [
            ['hor_codigo_externo' => 'DEMO-31', 'origen' => 'POR', 'destino' => 'MAP', 'servicio' => 'Vistadome', 'hor_hora_salida' => '06:40:00', 'hor_hora_llegada' => '10:12:00', 'hor_precio' => 420.00],
            ['hor_codigo_externo' => 'DEMO-33', 'origen' => 'POR', 'destino' => 'MAP', 'servicio' => 'Expedition', 'hor_hora_salida' => '07:35:00', 'hor_hora_llegada' => '11:15:00', 'hor_precio' => 350.00],
            ['hor_codigo_externo' => 'DEMO-81', 'origen' => 'OLL', 'destino' => 'MAP', 'servicio' => 'Expedition', 'hor_hora_salida' => '06:10:00', 'hor_hora_llegada' => '07:40:00', 'hor_precio' => 250.00],
            ['hor_codigo_externo' => 'DEMO-83', 'origen' => 'OLL', 'destino' => 'MAP', 'servicio' => 'Vistadome', 'hor_hora_salida' => '07:05:00', 'hor_hora_llegada' => '08:35:00', 'hor_precio' => 310.00],
            ['hor_codigo_externo' => 'DEMO-32', 'origen' => 'MAP', 'destino' => 'POR', 'servicio' => 'Vistadome', 'hor_hora_salida' => '15:20:00', 'hor_hora_llegada' => '19:04:00', 'hor_precio' => 420.00],
            ['hor_codigo_externo' => 'DEMO-84', 'origen' => 'MAP', 'destino' => 'OLL', 'servicio' => 'Expedition', 'hor_hora_salida' => '14:55:00', 'hor_hora_llegada' => '16:40:00', 'hor_precio' => 250.00],
            ['hor_codigo_externo' => 'DEMO-86', 'origen' => 'MAP', 'destino' => 'OLL', 'servicio' => 'Vistadome', 'hor_hora_salida' => '16:22:00', 'hor_hora_llegada' => '18:10:00', 'hor_precio' => 310.00],
            ['hor_codigo_externo' => 'DEMO-T1', 'origen' => 'WAN', 'destino' => 'PUN', 'servicio' => 'Titicaca', 'hor_hora_salida' => '07:10:00', 'hor_hora_llegada' => '17:40:00', 'hor_precio' => 1100.00],
            ['hor_codigo_externo' => 'DEMO-T2', 'origen' => 'PUN', 'destino' => 'WAN', 'servicio' => 'Titicaca', 'hor_hora_salida' => '07:10:00', 'hor_hora_llegada' => '17:40:00', 'hor_precio' => 1100.00],
        ];
    }
}
