<?php

namespace Database\Factories;

use App\Enums\Dificultad;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\ZonaTuristica;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ZonaTuristica>
 */
class ZonaTuristicaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zon_est_codigo' => Estacion::factory(),
            'zon_cat_codigo' => Categoria::factory(),
            'zon_nombre' => Str::title(fake()->unique()->words(3, true)),
            'zon_descripcion' => fake()->paragraph(),
            'zon_latitud' => fake()->latitude(-18, -3),
            'zon_longitud' => fake()->longitude(-81, -69),
            'zon_distancia' => fake()->numberBetween(200, 3000),
            'zon_dif_codigo' => fake()->randomElement(Dificultad::cases()),
            'zon_estado' => true,
        ];
    }
}
