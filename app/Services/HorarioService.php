<?php

namespace App\Services;

use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Servicio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Mantenimiento de horarios, tiempos de viaje y precios de los trenes (Módulo de Administración).
 */
class HorarioService
{
    /** @return list<array{codigo: int, codigo_externo: string, origen_codigo: int, destino_codigo: int, origen: string, destino: string, servicio: string, salida: string, llegada: string, duracion: string, precio: string, estado: bool}> */
    public function listar(): array
    {
        return Horario::query()
            ->with(['estacionOrigen', 'estacionDestino', 'servicio'])
            ->orderBy('hor_hora_salida')
            ->orderBy('hor_codigo')
            ->get()
            ->map(fn (Horario $horario): array => [
                'codigo' => $horario->getKey(),
                'codigo_externo' => $horario->hor_codigo_externo,
                'origen_codigo' => $horario->hor_est_codigo_origen,
                'destino_codigo' => $horario->hor_est_codigo_destino,
                'origen' => $horario->estacionOrigen->est_nombre,
                'destino' => $horario->estacionDestino->est_nombre,
                'servicio' => $horario->servicio->ser_nombre,
                'salida' => substr($horario->hor_hora_salida, 0, 5),
                'llegada' => substr($horario->hor_hora_llegada, 0, 5),
                'duracion' => $horario->duracionFormateada(),
                'precio' => $horario->hor_precio,
                'estado' => $horario->hor_estado,
            ])->all();
    }

    /** @return array<int, string> */
    public function estaciones(): array
    {
        return Estacion::query()
            ->where('est_estado', true)
            ->orderBy('est_nombre')
            ->pluck('est_nombre', 'est_codigo')
            ->all();
    }

    /**
     * Registra un servicio adicional o corrige uno existente. Los servicios creados
     * aquí reciben un código propio para no confundirse con los de PeruRail, y el nombre
     * del servicio se busca o registra en el catálogo tb_servicio.
     *
     * @param  array{hor_est_codigo_origen: int, hor_est_codigo_destino: int, hor_hora_salida: string, hor_hora_llegada: string, hor_precio: float}  $datos
     */
    public function guardar(?int $codigo, array $datos, string $servicio): Horario
    {
        return DB::transaction(function () use ($codigo, $datos, $servicio): Horario {
            $horario = $codigo === null
                ? new Horario(['hor_codigo_externo' => 'MTC-'.Str::upper(Str::random(8))])
                : Horario::query()->findOrFail($codigo);

            $horario->fill($datos);
            $horario->hor_ser_codigo = Servicio::query()->firstOrCreate(['ser_nombre' => $servicio])->getKey();
            $horario->hor_estado ??= true;
            $horario->save();

            return $horario;
        });
    }

    public function cambiarEstado(int $codigo): Horario
    {
        return DB::transaction(function () use ($codigo): Horario {
            $horario = Horario::query()->findOrFail($codigo);
            $horario->hor_estado = ! $horario->hor_estado;
            $horario->save();

            return $horario;
        });
    }
}
