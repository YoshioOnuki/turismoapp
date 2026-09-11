<?php

namespace Database\Seeders;

use App\Models\Clima;
use App\Models\Estacion;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class ClimaSeeder extends Seeder
{
    /**
     * Pronóstico de demostración para hoy y los dos días siguientes en cada estación.
     */
    public function run(): void
    {
        foreach (Estacion::all() as $estacion) {
            Clima::factory()
                ->count(3)
                ->for($estacion)
                ->sequence(fn (Sequence $sequence) => ['cli_fecha' => today()->addDays($sequence->index)])
                ->create();
        }
    }
}
