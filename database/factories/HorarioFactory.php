<?php

namespace Database\Factories;

use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Horario>
 */
class HorarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $salida = Carbon::today()->setTime(fake()->numberBetween(5, 15), fake()->randomElement([0, 15, 30, 45]));

        return [
            'hor_codigo_externo' => fake()->unique()->bothify('TREN-####'),
            'hor_est_codigo_origen' => Estacion::factory(),
            'hor_est_codigo_destino' => Estacion::factory(),
            'hor_ser_codigo' => Servicio::factory(),
            'hor_hora_salida' => $salida->format('H:i:s'),
            'hor_hora_llegada' => $salida->addMinutes(fake()->numberBetween(60, 240))->format('H:i:s'),
            'hor_precio' => fake()->randomFloat(2, 150, 600),
            'hor_estado' => true,
        ];
    }

    /**
     * Asigna el servicio con el nombre indicado, reutilizándolo si ya existe en tb_servicio.
     */
    public function conServicio(string $nombre): static
    {
        return $this->state(fn (): array => [
            'hor_ser_codigo' => Servicio::query()->firstOrCreate(['ser_nombre' => $nombre])->getKey(),
        ]);
    }
}
