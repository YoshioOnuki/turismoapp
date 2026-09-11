<?php

namespace App\Services;

use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class InformeService
{
    public function __construct(private PlanificacionService $planificacion) {}

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
            $informe = new Informe([
                'inf_est_codigo' => $estacion->getKey(),
                'inf_contenido' => [
                    'estacion' => $estacion->est_nombre,
                    'generado_en' => now()->toIso8601String(),
                    'zonas' => $zonas,
                    'trenes' => $informacion['trenes'],
                    'clima' => $informacion['clima'],
                ],
            ]);
            $informe->inf_usu_codigo = $usuario->getKey();
            $informe->save();
            $informe->categorias()->sync($categorias);

            return $informe;
        });
    }

    /** @return list<array{codigo: int, estacion: string, fecha: string, categorias: string, zonas: int, trenes: int}> */
    public function listar(Usuario $usuario): array
    {
        return Informe::query()
            ->with(['estacion', 'categorias'])
            ->where('inf_usu_codigo', $usuario->getKey())
            ->latest('inf_fecha_creacion')
            ->latest('inf_codigo')
            ->get()
            ->map(fn (Informe $informe): array => [
                'codigo' => $informe->getKey(),
                'estacion' => $informe->estacion->est_nombre,
                'fecha' => $informe->inf_fecha_creacion->format('d/m/Y H:i'),
                'categorias' => $informe->categorias->pluck('cat_nombre')->join(', '),
                'zonas' => count($informe->inf_contenido['zonas'] ?? []),
                'trenes' => count($informe->inf_contenido['trenes'] ?? []),
            ])->all();
    }
}
