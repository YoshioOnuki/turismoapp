<?php

namespace Database\Factories;

use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Informe>
 */
class InformeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inf_usu_codigo' => Usuario::factory(),
            'inf_est_codigo' => Estacion::factory(),
            'inf_contenido' => [
                'zonas' => [],
                'clima' => null,
                'trenes' => [],
            ],
        ];
    }
}
