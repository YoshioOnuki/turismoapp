<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Los catálogos se cargan siempre; los datos de demostración, solo fuera de producción.
     */
    public function run(): void
    {
        $this->call([
            CategoriaSeeder::class,
            ParametroSeeder::class,
        ]);

        if (app()->isProduction()) {
            return;
        }

        $this->call([
            UsuarioSeeder::class,
            EstacionSeeder::class,
            ZonaTuristicaSeeder::class,
            HorarioSeeder::class,
            ClimaSeeder::class,
        ]);
    }
}
