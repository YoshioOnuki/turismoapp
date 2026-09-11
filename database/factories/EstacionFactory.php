<?php

namespace Database\Factories;

use App\Models\Estacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estacion>
 */
class EstacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'est_codigo_externo' => fake()->unique()->bothify('EST-####'),
            'est_nombre' => 'Estación '.fake()->city(),
            'est_latitud' => fake()->latitude(-18, -3),
            'est_longitud' => fake()->longitude(-81, -69),
            'est_estado' => true,
        ];
    }
}
