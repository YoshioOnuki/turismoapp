<?php

namespace App\Services;

use App\Models\Clima;
use App\Models\Horario;
use App\Models\Informe;
use App\Models\ZonaTuristica;

class InformeExportacionService
{
    public function __construct(private PlanificacionService $planificacion) {}

    /**
     * Arma los datos del informe desde sus tablas de detalle para la pantalla y el PDF.
     *
     * @return array{
     *     informe: Informe,
     *     estacion: string,
     *     estacionMapa: array{codigo: int, nombre: string, latitud: float, longitud: float},
     *     categorias: string,
     *     categoriasLista: list<string>,
     *     fecha: string,
     *     zonas: list<array<string, mixed>>,
     *     trenes: list<array<string, mixed>>,
     *     clima: list<array<string, mixed>>,
     *     climaVigente: bool,
     *     actualizacion: array{trenes: ?string, clima: ?string},
     *     resumen: array{zonas: int, tiempo_minimo: ?int, distancia_minima: ?int, precio_desde: ?float}
     * }
     */
    public function preparar(Informe $informe): array
    {
        $informe->loadMissing([
            'estacion',
            'categorias',
            'zonas' => fn ($consulta) => $consulta->with(['categoria', 'imagenes' => fn ($imagenes) => $imagenes->orderBy('zim_orden')]),
            'horarios' => fn ($consulta) => $consulta->with(['estacionOrigen', 'servicio']),
            'climas',
        ]);

        $zonas = $informe->zonas
            ->sortBy([['pivot.izo_distancia_total', 'asc'], ['zon_nombre', 'asc']])
            ->map(fn (ZonaTuristica $zona): array => $this->planificacion->filaZona(
                $zona,
                (int) $zona->pivot->izo_distancia_total,
                (int) $zona->pivot->izo_tiempo_minutos,
            ))->values();
        $horarios = $informe->horarios->sortBy('hor_hora_llegada')->values();
        $climas = $informe->climas->sortBy('cli_fecha')->values();
        $trenes = $horarios->map(fn (Horario $horario): array => $this->planificacion->filaTren($horario, (string) $horario->pivot->iho_precio));
        $categorias = $informe->categorias->pluck('cat_nombre')->values();
        $estacion = $informe->estacion;

        return [
            'informe' => $informe,
            'estacion' => $estacion->est_nombre,
            'estacionMapa' => [
                'codigo' => $estacion->getKey(),
                'nombre' => $estacion->est_nombre,
                'latitud' => $estacion->est_latitud,
                'longitud' => $estacion->est_longitud,
            ],
            'categorias' => $categorias->join(', '),
            'categoriasLista' => $categorias->all(),
            'fecha' => $informe->inf_fecha_creacion->format('d/m/Y H:i'),
            'zonas' => $zonas->all(),
            'trenes' => $trenes->all(),
            'clima' => $climas->map(fn (Clima $clima): array => $this->planificacion->filaClima($clima))->all(),
            'climaVigente' => $climas->isEmpty()
                || $climas->contains(fn (Clima $clima): bool => $clima->cli_fecha->gte($informe->inf_fecha_creacion->copy()->startOfDay())),
            'actualizacion' => [
                'trenes' => $this->planificacion->fechaActualizacion($horarios, 'hor_fecha_actualizacion'),
                'clima' => $this->planificacion->fechaActualizacion($climas, 'cli_fecha_actualizacion'),
            ],
            'resumen' => [
                'zonas' => $zonas->count(),
                'tiempo_minimo' => $zonas->min('tiempo_minutos'),
                'distancia_minima' => $zonas->min('distancia_total'),
                'precio_desde' => $trenes->isEmpty() ? null : (float) $trenes->min(fn (array $tren): float => (float) $tren['precio']),
            ],
        ];
    }
}
