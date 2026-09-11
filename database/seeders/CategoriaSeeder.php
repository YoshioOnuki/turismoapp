<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Catálogo inicial de categorías turísticas (RF-05, RF-20).
     */
    public function run(): void
    {
        foreach (['Naturaleza', 'Historia', 'Aventura', 'Gastronomía'] as $nombre) {
            Categoria::firstOrCreate(['cat_nombre' => $nombre]);
        }
    }
}
