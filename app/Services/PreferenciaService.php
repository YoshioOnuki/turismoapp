<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class PreferenciaService
{
    /** @return array{categorias: list<array{codigo: int, nombre: string}>, seleccionadas: list<int>} */
    public function obtener(Usuario $usuario): array
    {
        return [
            'categorias' => Categoria::query()
                ->where('cat_estado', true)
                ->orderBy('cat_nombre')
                ->get()
                ->map(fn (Categoria $categoria): array => [
                    'codigo' => $categoria->getKey(),
                    'nombre' => $categoria->cat_nombre,
                ])->all(),
            'seleccionadas' => $usuario->preferencias()
                ->where('cat_estado', true)
                ->pluck('tb_categoria.cat_codigo')
                ->map(fn ($codigo): int => (int) $codigo)
                ->all(),
        ];
    }

    /** @param list<int> $categorias */
    public function guardar(Usuario $usuario, array $categorias): void
    {
        DB::transaction(fn () => $usuario->preferencias()->sync($categorias));
    }
}
