<?php

namespace App\Services;

use App\Models\Estacion;
use App\Models\Parametro;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use Illuminate\Support\Facades\Storage;

class PlanificacionService
{
    public const DISTANCIA_PREDETERMINADA = 3000;

    /** @return array<int, string> */
    public function estacionesActivas(): array
    {
        return Estacion::query()
            ->where('est_estado', true)
            ->orderBy('est_nombre')
            ->pluck('est_nombre', 'est_codigo')
            ->all();
    }

    /** @return list<array{codigo: int, nombre: string, descripcion: string, categoria: string, distancia: int, dificultad: string, imagen: ?string}> */
    public function zonasDisponibles(Usuario $usuario, int $estacionCodigo): array
    {
        $categorias = $usuario->preferencias()
            ->where('cat_estado', true)
            ->pluck('tb_categoria.cat_codigo');

        if ($categorias->isEmpty()) {
            return [];
        }

        $distanciaMaxima = $this->distanciaMaxima();

        return ZonaTuristica::query()
            ->with(['categoria', 'imagenes' => fn ($consulta) => $consulta->orderBy('zim_orden')->limit(1)])
            ->where('zon_est_codigo', $estacionCodigo)
            ->where('zon_estado', true)
            ->where('zon_distancia', '<=', $distanciaMaxima)
            ->whereIn('zon_cat_codigo', $categorias)
            ->orderBy('zon_distancia')
            ->orderBy('zon_nombre')
            ->get()
            ->map(fn (ZonaTuristica $zona): array => [
                'codigo' => $zona->getKey(),
                'nombre' => $zona->zon_nombre,
                'descripcion' => $zona->zon_descripcion,
                'categoria' => $zona->categoria->cat_nombre,
                'distancia' => $zona->zon_distancia,
                'dificultad' => $zona->zon_dificultad->value,
                'imagen' => $zona->imagenes->first() === null
                    ? null
                    : Storage::disk('public')->url($zona->imagenes->first()->zim_ruta),
            ])->all();
    }

    private function distanciaMaxima(): int
    {
        $valor = Parametro::query()
            ->where('par_clave', 'distancia_maxima_caminable')
            ->value('par_valor');

        return is_string($valor) && ctype_digit($valor) && (int) $valor > 0
            ? (int) $valor
            : self::DISTANCIA_PREDETERMINADA;
    }
}
