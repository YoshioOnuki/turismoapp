<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Parametro;
use Illuminate\Support\Facades\DB;

class ConfiguracionService
{
    /** @return array{parametros: list<array{codigo: int, clave: string, valor: string, descripcion: ?string}>, categorias: list<array{codigo: int, nombre: string, estado: bool, zonas: int}>} */
    public function listar(): array
    {
        return [
            'parametros' => Parametro::query()
                ->orderBy('par_clave')
                ->get()
                ->map(fn (Parametro $parametro): array => [
                    'codigo' => $parametro->getKey(),
                    'clave' => $parametro->par_clave,
                    'valor' => $parametro->par_valor,
                    'descripcion' => $parametro->par_descripcion,
                ])->all(),
            'categorias' => Categoria::query()
                ->withCount('zonasTuristicas')
                ->orderBy('cat_nombre')
                ->get()
                ->map(fn (Categoria $categoria): array => [
                    'codigo' => $categoria->getKey(),
                    'nombre' => $categoria->cat_nombre,
                    'estado' => $categoria->cat_estado,
                    'zonas' => $categoria->zonas_turisticas_count,
                ])->all(),
        ];
    }

    public function actualizarParametro(int $codigo, string $valor): Parametro
    {
        return DB::transaction(function () use ($codigo, $valor): Parametro {
            $parametro = Parametro::query()->findOrFail($codigo);
            $parametro->par_valor = $valor;
            $parametro->save();

            return $parametro;
        });
    }

    public function guardarCategoria(?int $codigo, string $nombre): Categoria
    {
        return DB::transaction(function () use ($codigo, $nombre): Categoria {
            $categoria = $codigo === null ? new Categoria : Categoria::query()->findOrFail($codigo);
            $categoria->cat_nombre = $nombre;
            $categoria->cat_estado ??= true;
            $categoria->save();

            return $categoria;
        });
    }

    public function cambiarEstadoCategoria(int $codigo): Categoria
    {
        return DB::transaction(function () use ($codigo): Categoria {
            $categoria = Categoria::query()->findOrFail($codigo);
            $categoria->cat_estado = ! $categoria->cat_estado;
            $categoria->save();

            return $categoria;
        });
    }
}
