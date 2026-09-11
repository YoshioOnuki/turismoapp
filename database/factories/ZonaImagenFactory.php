<?php

namespace Database\Factories;

use App\Models\ZonaImagen;
use App\Models\ZonaTuristica;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ZonaImagen>
 */
class ZonaImagenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zim_zon_codigo' => ZonaTuristica::factory(),
            'zim_ruta' => 'zonas/'.fake()->uuid().'.jpg',
            'zim_orden' => fake()->numberBetween(0, 9),
        ];
    }
}
