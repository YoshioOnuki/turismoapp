<?php

namespace App\Services;

use App\Models\Estacion;

class EstacionService
{
    /** @return list<array{codigo: int, codigo_externo: string, nombre: string, latitud: float, longitud: float, estado: bool, zonas_activas: int}> */
    public function listar(): array
    {
        return Estacion::query()
            ->withCount([
                'zonasTuristicas as zonas_activas_count' => fn ($consulta) => $consulta->where('zon_estado', true),
            ])
            ->orderBy('est_nombre')
            ->get()
            ->map(fn (Estacion $estacion): array => [
                'codigo' => $estacion->getKey(),
                'codigo_externo' => $estacion->est_codigo_externo,
                'nombre' => $estacion->est_nombre,
                'latitud' => $estacion->est_latitud,
                'longitud' => $estacion->est_longitud,
                'estado' => $estacion->est_estado,
                'zonas_activas' => $estacion->zonas_activas_count,
            ])->all();
    }
}
