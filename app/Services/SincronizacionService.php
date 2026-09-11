<?php

namespace App\Services;

use App\Enums\FuenteDatos;
use App\Enums\ResultadoSincronizacion;
use App\Enums\TipoSincronizacion;
use App\Models\Bitacora;
use App\Models\Clima;
use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Parametro;
use App\Models\Usuario;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use UnexpectedValueException;

class SincronizacionService
{
    public const FRECUENCIA_PREDETERMINADA = 24;

    public function __construct(
        private FuentePeruRail $peruRail,
        private FuenteSenamhi $senamhi,
    ) {}

    /**
     * Sincroniza las fuentes de forma independiente para que la falla de una no
     * impida actualizar la otra.
     *
     * @return array{perurail: Bitacora, senamhi: Bitacora}
     */
    public function ejecutar(TipoSincronizacion $tipo, ?Usuario $usuario = null): array
    {
        return [
            FuenteDatos::PeruRail->value => $this->sincronizarPeruRail($tipo, $usuario),
            FuenteDatos::Senamhi->value => $this->sincronizarSenamhi($tipo, $usuario),
        ];
    }

    /**
     * Ejecuta únicamente las fuentes que superaron la frecuencia configurada.
     * Una ejecución manual exitosa también renueva la vigencia de sus datos.
     *
     * @return array{perurail?: Bitacora, senamhi?: Bitacora}
     */
    public function ejecutarAutomaticamenteSiCorresponde(): array
    {
        $frecuencia = $this->frecuenciaEnHoras();
        $resultados = [];

        if ($this->requiereActualizacion(FuenteDatos::PeruRail, $frecuencia)) {
            $resultados[FuenteDatos::PeruRail->value] = $this->sincronizarPeruRail(TipoSincronizacion::Automatica, null);
        }

        if ($this->requiereActualizacion(FuenteDatos::Senamhi, $frecuencia)) {
            $resultados[FuenteDatos::Senamhi->value] = $this->sincronizarSenamhi(TipoSincronizacion::Automatica, null);
        }

        return $resultados;
    }

    /**
     * @return array{
     *     perurail: array{nombre: string, resultado: ?string, ultima_ejecucion: ?string, ultima_actualizacion: ?string, registros: int},
     *     senamhi: array{nombre: string, resultado: ?string, ultima_ejecucion: ?string, ultima_actualizacion: ?string, registros: int}
     * }
     */
    public function estadoFuentes(): array
    {
        return [
            FuenteDatos::PeruRail->value => $this->estadoFuente(FuenteDatos::PeruRail, 'PeruRail'),
            FuenteDatos::Senamhi->value => $this->estadoFuente(FuenteDatos::Senamhi, 'SENAMHI'),
        ];
    }

    private function sincronizarPeruRail(TipoSincronizacion $tipo, ?Usuario $usuario): Bitacora
    {
        $inicio = now();

        try {
            $estaciones = $this->peruRail->obtenerEstaciones();
            $horarios = $this->peruRail->obtenerHorarios();

            return DB::transaction(function () use ($estaciones, $horarios, $tipo, $usuario, $inicio): Bitacora {
                $registros = $this->guardarEstacionesYHorarios($estaciones, $horarios);

                return $this->registrarExito(FuenteDatos::PeruRail, $tipo, $usuario, $inicio, $registros);
            });
        } catch (Throwable $excepcion) {
            report($excepcion);

            return $this->registrarError(FuenteDatos::PeruRail, $tipo, $usuario, $inicio, $excepcion);
        }
    }

    private function sincronizarSenamhi(TipoSincronizacion $tipo, ?Usuario $usuario): Bitacora
    {
        $inicio = now();

        try {
            $pronosticos = $this->senamhi->obtenerPronosticos();

            return DB::transaction(function () use ($pronosticos, $tipo, $usuario, $inicio): Bitacora {
                $registros = $this->guardarPronosticos($pronosticos);

                return $this->registrarExito(FuenteDatos::Senamhi, $tipo, $usuario, $inicio, $registros);
            });
        } catch (Throwable $excepcion) {
            report($excepcion);

            return $this->registrarError(FuenteDatos::Senamhi, $tipo, $usuario, $inicio, $excepcion);
        }
    }

    /**
     * @param  list<EstacionExterna>  $estaciones
     * @param  list<HorarioExterno>  $horarios
     */
    private function guardarEstacionesYHorarios(array $estaciones, array $horarios): int
    {
        foreach ($estaciones as $estacion) {
            Estacion::updateOrCreate(
                ['est_codigo_externo' => $estacion->codigoExterno],
                [
                    'est_nombre' => $estacion->nombre,
                    'est_latitud' => $estacion->latitud,
                    'est_longitud' => $estacion->longitud,
                    'est_estado' => true,
                ],
            );
        }

        $codigosExternos = collect($estaciones)->pluck('codigoExterno');
        $estacionesPorCodigo = Estacion::query()
            ->whereIn('est_codigo_externo', $codigosExternos)
            ->pluck('est_codigo', 'est_codigo_externo');

        foreach ($horarios as $horario) {
            $codigoOrigen = $estacionesPorCodigo->get($horario->codigoEstacionOrigen);
            $codigoDestino = $estacionesPorCodigo->get($horario->codigoEstacionDestino);

            if ($codigoOrigen === null || $codigoDestino === null) {
                throw new UnexpectedValueException("El horario {$horario->codigoExterno} referencia una estación desconocida.");
            }

            Horario::updateOrCreate(
                ['hor_codigo_externo' => $horario->codigoExterno],
                [
                    'hor_est_codigo_origen' => $codigoOrigen,
                    'hor_est_codigo_destino' => $codigoDestino,
                    'hor_servicio' => $horario->servicio,
                    'hor_hora_salida' => $horario->horaSalida,
                    'hor_hora_llegada' => $horario->horaLlegada,
                    'hor_precio' => $horario->precio,
                    'hor_estado' => true,
                ],
            );
        }

        return count($estaciones) + count($horarios);
    }

    /**
     * @param  list<PronosticoExterno>  $pronosticos
     */
    private function guardarPronosticos(array $pronosticos): int
    {
        $estacionesPorCodigo = Estacion::query()
            ->whereIn('est_codigo_externo', collect($pronosticos)->pluck('codigoEstacion')->unique())
            ->pluck('est_codigo', 'est_codigo_externo');

        foreach ($pronosticos as $pronostico) {
            $codigoEstacion = $estacionesPorCodigo->get($pronostico->codigoEstacion);

            if ($codigoEstacion === null) {
                throw new UnexpectedValueException("El pronóstico referencia la estación desconocida {$pronostico->codigoEstacion}.");
            }

            Clima::updateOrCreate(
                [
                    'cli_est_codigo' => $codigoEstacion,
                    'cli_fecha' => $pronostico->fecha,
                ],
                [
                    'cli_temperatura_minima' => $pronostico->temperaturaMinima,
                    'cli_temperatura_maxima' => $pronostico->temperaturaMaxima,
                    'cli_probabilidad_lluvia' => $pronostico->probabilidadLluvia,
                    'cli_descripcion' => $pronostico->descripcion,
                ],
            );
        }

        return count($pronosticos);
    }

    private function registrarExito(
        FuenteDatos $fuente,
        TipoSincronizacion $tipo,
        ?Usuario $usuario,
        DateTimeInterface $inicio,
        int $registros,
    ): Bitacora {
        return Bitacora::create([
            'bit_usu_codigo' => $usuario?->getKey(),
            'bit_fuente' => $fuente,
            'bit_tipo' => $tipo,
            'bit_fecha_inicio' => $inicio,
            'bit_fecha_fin' => now(),
            'bit_registros' => $registros,
            'bit_resultado' => ResultadoSincronizacion::Exito,
            'bit_mensaje' => 'Sincronización completada.',
        ]);
    }

    private function registrarError(
        FuenteDatos $fuente,
        TipoSincronizacion $tipo,
        ?Usuario $usuario,
        DateTimeInterface $inicio,
        Throwable $excepcion,
    ): Bitacora {
        return Bitacora::create([
            'bit_usu_codigo' => $usuario?->getKey(),
            'bit_fuente' => $fuente,
            'bit_tipo' => $tipo,
            'bit_fecha_inicio' => $inicio,
            'bit_fecha_fin' => now(),
            'bit_registros' => 0,
            'bit_resultado' => ResultadoSincronizacion::Error,
            'bit_mensaje' => Str::limit($excepcion->getMessage(), 1000),
        ]);
    }

    private function frecuenciaEnHoras(): int
    {
        $valor = Parametro::query()
            ->where('par_clave', 'frecuencia_sincronizacion')
            ->value('par_valor');

        if (! is_string($valor) || ! ctype_digit($valor) || (int) $valor < 1) {
            return self::FRECUENCIA_PREDETERMINADA;
        }

        return (int) $valor;
    }

    private function requiereActualizacion(FuenteDatos $fuente, int $frecuencia): bool
    {
        $ultimaEjecucion = Bitacora::query()
            ->where('bit_fuente', $fuente->value)
            ->where('bit_resultado', ResultadoSincronizacion::Exito->value)
            ->max('bit_fecha_fin');

        return $ultimaEjecucion === null
            || Carbon::parse($ultimaEjecucion)->lte(now()->subHours($frecuencia));
    }

    /**
     * @return array{nombre: string, resultado: ?string, ultima_ejecucion: ?string, ultima_actualizacion: ?string, registros: int}
     */
    private function estadoFuente(FuenteDatos $fuente, string $nombre): array
    {
        $ultimoIntento = Bitacora::query()
            ->where('bit_fuente', $fuente->value)
            ->latest('bit_fecha_fin')
            ->latest('bit_codigo')
            ->first();

        $ultimaActualizacion = Bitacora::query()
            ->where('bit_fuente', $fuente->value)
            ->where('bit_resultado', ResultadoSincronizacion::Exito->value)
            ->max('bit_fecha_fin');

        return [
            'nombre' => $nombre,
            'resultado' => $ultimoIntento?->bit_resultado?->value,
            'ultima_ejecucion' => $ultimoIntento?->bit_fecha_fin?->format('d/m/Y H:i'),
            'ultima_actualizacion' => $ultimaActualizacion === null
                ? null
                : Carbon::parse($ultimaActualizacion)->format('d/m/Y H:i'),
            'registros' => $ultimoIntento?->bit_registros ?? 0,
        ];
    }
}
