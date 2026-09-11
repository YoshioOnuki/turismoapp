<?php

namespace App\Services;

use App\Models\Estacion;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    /** @return list<array{codigo: int, estacion: string, codigo_externo: string, zonas: string, total: int}> */
    public function zonasPorEstacion(): array
    {
        return Estacion::query()
            ->with(['zonasTuristicas' => fn ($consulta) => $consulta
                ->where('zon_estado', true)
                ->orderBy('zon_nombre')])
            ->orderBy('est_nombre')
            ->get()
            ->map(fn (Estacion $estacion): array => [
                'codigo' => $estacion->getKey(),
                'estacion' => $estacion->est_nombre,
                'codigo_externo' => $estacion->est_codigo_externo,
                'zonas' => $estacion->zonasTuristicas->pluck('zon_nombre')->join(', '),
                'total' => $estacion->zonasTuristicas->count(),
            ])->all();
    }

    /** @return array{estaciones: list<array{codigo: int, nombre: string, consultas: int}>, categorias: list<array{codigo: int, nombre: string, consultas: int}>} */
    public function uso(): array
    {
        $estaciones = DB::table('tb_informe')
            ->join('tb_estacion', 'tb_estacion.est_codigo', '=', 'tb_informe.inf_est_codigo')
            ->select('tb_estacion.est_codigo as codigo', 'tb_estacion.est_nombre as nombre')
            ->selectRaw('COUNT(*) as consultas')
            ->groupBy('tb_estacion.est_codigo', 'tb_estacion.est_nombre')
            ->orderByDesc('consultas')
            ->orderBy('tb_estacion.est_nombre')
            ->get()
            ->map(fn ($fila): array => [
                'codigo' => (int) $fila->codigo,
                'nombre' => $fila->nombre,
                'consultas' => (int) $fila->consultas,
            ])->all();

        $categorias = DB::table('tb_informe_categoria')
            ->join('tb_categoria', 'tb_categoria.cat_codigo', '=', 'tb_informe_categoria.ica_cat_codigo')
            ->select('tb_categoria.cat_codigo as codigo', 'tb_categoria.cat_nombre as nombre')
            ->selectRaw('COUNT(*) as consultas')
            ->groupBy('tb_categoria.cat_codigo', 'tb_categoria.cat_nombre')
            ->orderByDesc('consultas')
            ->orderBy('tb_categoria.cat_nombre')
            ->get()
            ->map(fn ($fila): array => [
                'codigo' => (int) $fila->codigo,
                'nombre' => $fila->nombre,
                'consultas' => (int) $fila->consultas,
            ])->all();

        return compact('estaciones', 'categorias');
    }
}
