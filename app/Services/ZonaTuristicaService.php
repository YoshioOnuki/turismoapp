<?php

namespace App\Services;

use App\Enums\FuenteDatos;
use App\Enums\ResultadoSincronizacion;
use App\Enums\TipoSincronizacion;
use App\Models\Bitacora;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Usuario;
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
                'dificultad_codigo' => $zona->zon_dif_codigo->value,
                'dificultad' => $zona->zon_dif_codigo->etiqueta(),
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
     * Registra o actualiza una zona con sus imágenes y deja constancia de la carga
     * de Travel Group Perú en la bitácora (RF-14, RF-16).
     *
     * @param  array<string, mixed>  $datos
     * @param  list<UploadedFile>  $imagenes
     */
    public function guardar(?int $codigo, array $datos, array $imagenes, ?Usuario $usuario = null): ZonaTuristica
    {
        $inicio = now();
        $rutas = [];

        try {
            foreach ($imagenes as $imagen) {
                $rutas[] = $imagen->store('zonas', 'public');
            }

            return DB::transaction(function () use ($codigo, $datos, $rutas, $usuario, $inicio): ZonaTuristica {
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

                Bitacora::create([
                    'bit_usu_codigo' => $usuario?->getKey(),
                    'bit_fue_codigo' => FuenteDatos::TravelGroup,
                    'bit_tsi_codigo' => TipoSincronizacion::Manual,
                    'bit_fecha_inicio' => $inicio,
                    'bit_fecha_fin' => now(),
                    'bit_registros' => 1,
                    'bit_res_codigo' => ResultadoSincronizacion::Exito,
                    'bit_mensaje' => ($codigo === null ? 'Zona registrada: ' : 'Zona actualizada: ').$zona->zon_nombre,
                ]);

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
