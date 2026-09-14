<?php

namespace App\Services;

use App\Models\Clima;
use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Parametro;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
            ->map(fn (ZonaTuristica $zona): array => $this->filaZona(
                $zona,
                $zona->zon_distancia * 2,
                (int) ceil(($zona->zon_distancia * 2) / ($velocidad * 1000 / 60)),
            ))->all();
    }

    /**
     * Reúne los trenes que llegan a la estación y su pronóstico. Si el SENAMHI no
     * entregó un pronóstico vigente, devuelve el último disponible y lo indica (RNF-09).
     *
     * @return array{
     *     estacion: array{codigo: int, nombre: string, latitud: float, longitud: float},
     *     trenes: list<array{codigo: int, origen: string, servicio: string, salida: string, llegada: string, duracion: string, precio: string}>,
     *     clima: list<array{codigo: int, fecha: string, descripcion: string, minima: string, maxima: string, lluvia: int}>,
     *     clima_vigente: bool,
     *     actualizacion: array{trenes: ?string, clima: ?string}
     * }
     */
    public function informacionEstacion(int $estacionCodigo): array
    {
        $estacion = Estacion::query()->where('est_estado', true)->findOrFail($estacionCodigo);

        $horarios = Horario::query()
            ->with(['estacionOrigen', 'servicio'])
            ->where('hor_est_codigo_destino', $estacionCodigo)
            ->where('hor_estado', true)
            ->orderBy('hor_hora_llegada')
            ->get();

        $pronosticos = Clima::query()
            ->where('cli_est_codigo', $estacionCodigo)
            ->whereDate('cli_fecha', '>=', today())
            ->orderBy('cli_fecha')
            ->limit(3)
            ->get();
        $climaVigente = $pronosticos->isNotEmpty();

        if (! $climaVigente) {
            $pronosticos = Clima::query()
                ->where('cli_est_codigo', $estacionCodigo)
                ->orderByDesc('cli_fecha')
                ->limit(3)
                ->get()
                ->reverse()
                ->values();
        }

        return [
            'estacion' => [
                'codigo' => $estacion->getKey(),
                'nombre' => $estacion->est_nombre,
                'latitud' => $estacion->est_latitud,
                'longitud' => $estacion->est_longitud,
            ],
            'trenes' => $horarios->map(fn (Horario $horario): array => $this->filaTren($horario, $horario->hor_precio))->all(),
            'clima' => $pronosticos->map(fn (Clima $clima): array => $this->filaClima($clima))->all(),
            'clima_vigente' => $climaVigente,
            'actualizacion' => [
                'trenes' => $this->fechaActualizacion($horarios, 'hor_fecha_actualizacion'),
                'clima' => $this->fechaActualizacion($pronosticos, 'cli_fecha_actualizacion'),
            ],
        ];
    }

    /**
     * Datos de una zona para la pantalla y el informe. La zona debe tener cargadas su
     * categoría e imágenes.
     *
     * @return array{codigo: int, nombre: string, descripcion: string, categoria: string, latitud: float, longitud: float, distancia: int, distancia_total: int, tiempo_minutos: int, dificultad: string, imagen: ?string}
     */
    public function filaZona(ZonaTuristica $zona, int $distanciaTotal, int $tiempoMinutos): array
    {
        $imagen = $zona->imagenes->first();

        return [
            'codigo' => $zona->getKey(),
            'nombre' => $zona->zon_nombre,
            'descripcion' => $zona->zon_descripcion,
            'categoria' => $zona->categoria->cat_nombre,
            'latitud' => $zona->zon_latitud,
            'longitud' => $zona->zon_longitud,
            'distancia' => intdiv($distanciaTotal, 2),
            'distancia_total' => $distanciaTotal,
            'tiempo_minutos' => $tiempoMinutos,
            'dificultad' => $zona->zon_dif_codigo->etiqueta(),
            'imagen' => $imagen === null ? null : Storage::disk('public')->url($imagen->zim_ruta),
        ];
    }

    /**
     * Datos de un tren con el precio indicado. El horario debe tener cargados su estación
     * de origen y su servicio.
     *
     * @return array{codigo: int, origen: string, servicio: string, salida: string, llegada: string, duracion: string, precio: string}
     */
    public function filaTren(Horario $horario, string $precio): array
    {
        return [
            'codigo' => $horario->getKey(),
            'origen' => $horario->estacionOrigen->est_nombre,
            'servicio' => $horario->servicio->ser_nombre,
            'salida' => substr($horario->hor_hora_salida, 0, 5),
            'llegada' => substr($horario->hor_hora_llegada, 0, 5),
            'duracion' => $horario->duracionFormateada(),
            'precio' => number_format((float) $precio, 2, '.', ''),
        ];
    }

    /** @return array{codigo: int, fecha: string, descripcion: string, minima: string, maxima: string, lluvia: int} */
    public function filaClima(Clima $clima): array
    {
        return [
            'codigo' => $clima->getKey(),
            'fecha' => $clima->cli_fecha->translatedFormat('d M'),
            'descripcion' => $clima->cli_descripcion,
            'minima' => $clima->cli_temperatura_minima,
            'maxima' => $clima->cli_temperatura_maxima,
            'lluvia' => $clima->cli_probabilidad_lluvia,
        ];
    }

    /**
     * Fecha más reciente en que la fuente confirmó alguno de los registros indicados.
     *
     * @param  Collection<int, Clima|Horario>  $registros
     */
    public function fechaActualizacion(Collection $registros, string $columna): ?string
    {
        $fecha = $registros->max(fn (Clima|Horario $registro) => $registro->getAttribute($columna));

        return $fecha === null ? null : Carbon::parse($fecha)->format('d/m/Y H:i');
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
