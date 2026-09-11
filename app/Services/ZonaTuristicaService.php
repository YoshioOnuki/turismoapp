<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\ZonaTuristica;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ZonaTuristicaService
{
    /** @return list<array<string, mixed>> */
    public function listar(): array
    {
        return ZonaTuristica::query()
            ->with(['estacion', 'categoria', 'imagenes' => fn ($consulta) => $consulta->orderBy('zim_orden')])
            ->orderBy('zon_nombre')
            ->get()
            ->map(fn (ZonaTuristica $zona): array => [
                'codigo' => $zona->getKey(),
                'estacion_codigo' => $zona->zon_est_codigo,
                'categoria_codigo' => $zona->zon_cat_codigo,
                'nombre' => $zona->zon_nombre,
                'descripcion' => $zona->zon_descripcion,
                'latitud' => $zona->zon_latitud,
                'longitud' => $zona->zon_longitud,
                'distancia' => $zona->zon_distancia,
                'dificultad' => $zona->zon_dificultad->value,
                'estado' => $zona->zon_estado,
                'estacion' => $zona->estacion->est_nombre,
                'categoria' => $zona->categoria->cat_nombre,
                'imagenes' => $zona->imagenes->map(fn ($imagen): array => [
                    'ruta' => $imagen->zim_ruta,
                    'url' => Storage::disk('public')->url($imagen->zim_ruta),
                ])->all(),
            ])->all();
    }

    /** @return array{estaciones: array<int, string>, categorias: array<int, string>} */
    public function catalogos(): array
    {
        return [
            'estaciones' => Estacion::query()->where('est_estado', true)->orderBy('est_nombre')->pluck('est_nombre', 'est_codigo')->all(),
            'categorias' => Categoria::query()->where('cat_estado', true)->orderBy('cat_nombre')->pluck('cat_nombre', 'cat_codigo')->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  list<UploadedFile>  $imagenes
     */
    public function guardar(?int $codigo, array $datos, array $imagenes): ZonaTuristica
    {
        $rutas = [];

        try {
            foreach ($imagenes as $imagen) {
                $rutas[] = $imagen->store('zonas', 'public');
            }

            return DB::transaction(function () use ($codigo, $datos, $rutas): ZonaTuristica {
                $zona = $codigo === null
                    ? new ZonaTuristica
                    : ZonaTuristica::query()->findOrFail($codigo);

                $zona->fill($datos);
                $zona->zon_estado ??= true;
                $zona->save();

                $ordenInicial = (int) $zona->imagenes()->max('zim_orden') + 1;

                foreach ($rutas as $indice => $ruta) {
                    $zona->imagenes()->create([
                        'zim_ruta' => $ruta,
                        'zim_orden' => $ordenInicial + $indice,
                    ]);
                }

                return $zona->load(['estacion', 'categoria', 'imagenes']);
            });
        } catch (Throwable $excepcion) {
            Storage::disk('public')->delete($rutas);

            throw $excepcion;
        }
    }

    public function cambiarEstado(int $codigo): ZonaTuristica
    {
        return DB::transaction(function () use ($codigo): ZonaTuristica {
            $zona = ZonaTuristica::query()->findOrFail($codigo);
            $zona->zon_estado = ! $zona->zon_estado;
            $zona->save();

            return $zona;
        });
    }
}
