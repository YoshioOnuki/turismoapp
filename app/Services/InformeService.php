<?php

namespace App\Services;

use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class InformeService
{
    public function __construct(private PlanificacionService $planificacion) {}

    /**
     * Guarda el informe consolidado (RF-21) con sus zonas, trenes y pronósticos en las tablas
     * de detalle, junto con el recorrido, el tiempo y el precio calculados en ese momento.
     */
    public function generar(Usuario $usuario, int $estacionCodigo): Informe
    {
        $estacion = Estacion::query()
            ->where('est_estado', true)
            ->findOrFail($estacionCodigo);
        $informacion = $this->planificacion->informacionEstacion($estacionCodigo);
        $zonas = $this->planificacion->zonasDisponibles($usuario, $estacionCodigo);
        $categorias = $usuario->preferencias()
            ->where('cat_estado', true)
            ->pluck('tb_categoria.cat_codigo');

        return DB::transaction(function () use ($usuario, $estacion, $informacion, $zonas, $categorias): Informe {
            $informe = new Informe(['inf_est_codigo' => $estacion->getKey()]);
            $informe->inf_usu_codigo = $usuario->getKey();
            $informe->save();

            $informe->categorias()->sync($categorias);
            $informe->zonas()->attach(collect($zonas)->mapWithKeys(fn (array $zona): array => [
                $zona['codigo'] => [
                    'izo_distancia_total' => $zona['distancia_total'],
                    'izo_tiempo_minutos' => $zona['tiempo_minutos'],
                ],
            ])->all());
            $informe->horarios()->attach(collect($informacion['trenes'])->mapWithKeys(fn (array $tren): array => [
                $tren['codigo'] => ['iho_precio' => $tren['precio']],
            ])->all());
            $informe->climas()->attach(array_column($informacion['clima'], 'codigo'));

            return $informe;
        });
    }

    /** @return list<array{codigo: int, estacion: string, fecha: string, categorias: string, zonas: int, trenes: int}> */
    public function listar(Usuario $usuario): array
    {
        return Informe::query()
            ->with(['estacion', 'categorias'])
            ->withCount(['zonas', 'horarios'])
            ->where('inf_usu_codigo', $usuario->getKey())
            ->latest('inf_fecha_creacion')
            ->latest('inf_codigo')
            ->get()
            ->map(fn (Informe $informe): array => [
                'codigo' => $informe->getKey(),
                'estacion' => $informe->estacion->est_nombre,
                'fecha' => $informe->inf_fecha_creacion->format('d/m/Y H:i'),
                'categorias' => $informe->categorias->pluck('cat_nombre')->join(', '),
                'zonas' => $informe->zonas_count,
                'trenes' => $informe->horarios_count,
            ])->all();
    }
}
