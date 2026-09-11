<?php

namespace Database\Factories;

use App\Models\Parametro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Parametro>
 */
class ParametroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'par_clave' => fake()->unique()->lexify('parametro_??????'),
            'par_valor' => (string) fake()->numberBetween(1, 100),
            'par_descripcion' => fake()->sentence(),
        ];
    }
}
