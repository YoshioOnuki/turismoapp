<?php

namespace Database\Factories;

use App\Enums\FuenteDatos;
use App\Enums\ResultadoSincronizacion;
use App\Enums\TipoSincronizacion;
use App\Models\Bitacora;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Bitacora>
 */
class BitacoraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = Carbon::now()->subMinutes(fake()->numberBetween(5, 120));

        return [
            'bit_usu_codigo' => null,
            'bit_fue_codigo' => fake()->randomElement(FuenteDatos::cases()),
            'bit_tsi_codigo' => TipoSincronizacion::Automatica,
            'bit_fecha_inicio' => $inicio->copy(),
            'bit_fecha_fin' => $inicio->addSeconds(fake()->numberBetween(5, 90)),
            'bit_registros' => fake()->numberBetween(0, 500),
            'bit_res_codigo' => ResultadoSincronizacion::Exito,
            'bit_mensaje' => null,
        ];
    }
}
