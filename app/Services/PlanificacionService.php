<?php

namespace App\Services;

use App\Models\Clima;
use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Parametro;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use Illuminate\Support\Facades\Storage;

class PlanificacionService
{
    public const DISTANCIA_PREDETERMINADA = 3000;

    public const VELOCIDAD_PREDETERMINADA = 4.0;

    /** @return array<int, string> */
    public function estacionesActivas(): array
    {
        return Estacion::query()
            ->where('est_estado', true)
            ->orderBy('est_nombre')
            ->pluck('est_nombre', 'est_codigo')
            ->all();
    }

    /** @return list<array{codigo: int, nombre: string, descripcion: string, categoria: string, latitud: float, longitud: float, distancia: int, distancia_total: int, tiempo_minutos: int, dificultad: string, imagen: ?string}> */
    public function zonasDisponibles(Usuario $usuario, int $estacionCodigo): array
    {
        $categorias = $usuario->preferencias()
            ->where('cat_estado', true)
            ->pluck('tb_categoria.cat_codigo');

        if ($categorias->isEmpty()) {
            return [];
        }

        $distanciaMaxima = $this->distanciaMaxima();
        $velocidad = $this->velocidadCaminata();

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
                'latitud' => $zona->zon_latitud,
                'longitud' => $zona->zon_longitud,
                'distancia' => $zona->zon_distancia,
                'distancia_total' => $zona->zon_distancia * 2,
                'tiempo_minutos' => (int) ceil(($zona->zon_distancia * 2) / ($velocidad * 1000 / 60)),
                'dificultad' => $zona->zon_dificultad->value,
                'imagen' => $zona->imagenes->first() === null
                    ? null
                    : Storage::disk('public')->url($zona->imagenes->first()->zim_ruta),
            ])->all();
    }

    /**
     * @return array{
     *     estacion: array{codigo: int, nombre: string, latitud: float, longitud: float},
     *     trenes: list<array{codigo: int, origen: string, servicio: string, salida: string, llegada: string, precio: string}>,
     *     clima: list<array{codigo: int, fecha: string, descripcion: string, minima: string, maxima: string, lluvia: int}>
     * }
     */
    public function informacionEstacion(int $estacionCodigo): array
    {
        $estacion = Estacion::query()->where('est_estado', true)->findOrFail($estacionCodigo);

        return [
            'estacion' => [
                'codigo' => $estacion->getKey(),
                'nombre' => $estacion->est_nombre,
                'latitud' => $estacion->est_latitud,
                'longitud' => $estacion->est_longitud,
            ],
            'trenes' => Horario::query()
                ->with('estacionOrigen')
                ->where('hor_est_codigo_destino', $estacionCodigo)
                ->where('hor_estado', true)
                ->orderBy('hor_hora_llegada')
                ->get()
                ->map(fn (Horario $horario): array => [
                    'codigo' => $horario->getKey(),
                    'origen' => $horario->estacionOrigen->est_nombre,
                    'servicio' => $horario->hor_servicio,
                    'salida' => substr($horario->hor_hora_salida, 0, 5),
                    'llegada' => substr($horario->hor_hora_llegada, 0, 5),
                    'precio' => $horario->hor_precio,
                ])->all(),
            'clima' => Clima::query()
                ->where('cli_est_codigo', $estacionCodigo)
                ->whereDate('cli_fecha', '>=', today())
                ->orderBy('cli_fecha')
                ->limit(3)
                ->get()
                ->map(fn (Clima $clima): array => [
                    'codigo' => $clima->getKey(),
                    'fecha' => $clima->cli_fecha->translatedFormat('d M'),
                    'descripcion' => $clima->cli_descripcion,
                    'minima' => $clima->cli_temperatura_minima,
                    'maxima' => $clima->cli_temperatura_maxima,
                    'lluvia' => $clima->cli_probabilidad_lluvia,
                ])->all(),
        ];
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

    private function velocidadCaminata(): float
    {
        $valor = Parametro::query()
            ->where('par_clave', 'velocidad_caminata')
            ->value('par_valor');

        return is_numeric($valor) && (float) $valor > 0
            ? (float) $valor
            : self::VELOCIDAD_PREDETERMINADA;
    }
}
