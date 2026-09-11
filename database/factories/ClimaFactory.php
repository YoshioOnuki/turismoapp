<?php

namespace Database\Factories;

use App\Models\Clima;
use App\Models\Estacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Clima>
 */
class ClimaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $temperaturaMinima = fake()->randomFloat(1, -2, 12);

        return [
            'cli_est_codigo' => Estacion::factory(),
            'cli_fecha' => today(),
            'cli_temperatura_minima' => $temperaturaMinima,
            'cli_temperatura_maxima' => round($temperaturaMinima + fake()->randomFloat(1, 6, 14), 1),
            'cli_probabilidad_lluvia' => fake()->numberBetween(0, 100),
            'cli_descripcion' => fake()->randomElement([
                'Cielo despejado',
                'Parcialmente nublado',
                'Nublado con lluvia ligera',
                'Lluvia moderada por la tarde',
            ]),
        ];
    }
}
