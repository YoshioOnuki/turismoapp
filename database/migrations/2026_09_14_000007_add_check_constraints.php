<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Reglas de dominio que la base de datos verifica además de la aplicación (RNF-14).
     *
     * @var array<string, array<string, string>>
     */
    private const RESTRICCIONES = [
        'tb_estacion' => [
            'chk_estacion_latitud' => 'est_latitud BETWEEN -90 AND 90',
            'chk_estacion_longitud' => 'est_longitud BETWEEN -180 AND 180',
        ],
        'tb_zona_turistica' => [
            'chk_zona_turistica_latitud' => 'zon_latitud BETWEEN -90 AND 90',
            'chk_zona_turistica_longitud' => 'zon_longitud BETWEEN -180 AND 180',
            'chk_zona_turistica_distancia' => 'zon_distancia > 0',
        ],
        'tb_clima' => [
            'chk_clima_probabilidad_lluvia' => 'cli_probabilidad_lluvia BETWEEN 0 AND 100',
            'chk_clima_temperaturas' => 'cli_temperatura_minima <= cli_temperatura_maxima',
        ],
        'tb_horario' => [
            'chk_horario_precio' => 'hor_precio >= 0',
            'chk_horario_estaciones_distintas' => 'hor_est_codigo_origen <> hor_est_codigo_destino',
        ],
        'tb_informe_zona' => [
            'chk_informe_zona_distancia_total' => 'izo_distancia_total > 0',
        ],
        'tb_informe_horario' => [
            'chk_informe_horario_precio' => 'iho_precio >= 0',
        ],
        'tb_bitacora' => [
            'chk_bitacora_fechas' => 'bit_fecha_fin >= bit_fecha_inicio',
        ],
    ];

    /**
     * Run the migrations.
     *
     * Solo MySQL y MariaDB permiten agregar restricciones CHECK a una tabla existente. SQLite,
     * usado en las pruebas en memoria, conserva estas reglas en la validación de la aplicación.
     */
    public function up(): void
    {
        if (! $this->admiteRestriccionesCheck()) {
            return;
        }

        foreach (self::RESTRICCIONES as $tabla => $restricciones) {
            foreach ($restricciones as $nombre => $condicion) {
                DB::statement("alter table {$tabla} add constraint {$nombre} check ({$condicion})");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->admiteRestriccionesCheck()) {
            return;
        }

        foreach (self::RESTRICCIONES as $tabla => $restricciones) {
            foreach (array_keys($restricciones) as $nombre) {
                DB::statement("alter table {$tabla} drop constraint {$nombre}");
            }
        }
    }

    private function admiteRestriccionesCheck(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
