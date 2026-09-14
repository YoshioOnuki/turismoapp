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
use App\Models\Servicio;
use App\Models\Usuario;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
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
     * @return array<int, Bitacora>
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
     * @return array<int, Bitacora>
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
     * @return array<int, array{nombre: string, resultado: ?string, ultima_ejecucion: ?string, ultima_actualizacion: ?string, registros: int}>
     */
    public function estadoFuentes(): array
    {
        return [
            FuenteDatos::PeruRail->value => $this->estadoFuente(FuenteDatos::PeruRail),
            FuenteDatos::Senamhi->value => $this->estadoFuente(FuenteDatos::Senamhi),
        ];
    }

    /**
     * Últimas ejecuciones registradas en la bitácora, de la más reciente a la más antigua (RF-14).
     *
     * @return list<array{codigo: int, fecha: string, fuente: string, tipo: string, usuario: ?string, registros: int, resultado: string, mensaje: ?string}>
     */
    public function bitacoraReciente(int $limite = 20): array
    {
        return Bitacora::query()
            ->with('usuario')
            ->latest('bit_fecha_fin')
            ->latest('bit_codigo')
            ->limit($limite)
            ->get()
            ->map(fn (Bitacora $bitacora): array => [
                'codigo' => $bitacora->getKey(),
                'fecha' => $bitacora->bit_fecha_fin->format('d/m/Y H:i'),
                'fuente' => $bitacora->bit_fue_codigo->etiqueta(),
                'tipo' => $bitacora->bit_tsi_codigo->etiqueta(),
                'usuario' => $bitacora->usuario?->usu_nombre,
                'registros' => $bitacora->bit_registros,
                'resultado' => $this->claveResultado($bitacora->bit_res_codigo),
                'mensaje' => $bitacora->bit_mensaje,
            ])->all();
    }

    /**
     * Renueva la fecha de actualización de un registro que la fuente volvió a entregar
     * sin cambios, para informar con precisión la vigencia de los datos (RNF-09).
     */
    private function confirmarVigencia(Model $registro): void
    {
        if (! $registro->wasRecentlyCreated && ! $registro->wasChanged()) {
            $registro->touch();
        }
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
            $registro = Estacion::updateOrCreate(
                ['est_codigo_externo' => $estacion->codigoExterno],
                [
                    'est_nombre' => $estacion->nombre,
                    'est_latitud' => $estacion->latitud,
                    'est_longitud' => $estacion->longitud,
                    'est_estado' => true,
                ],
            );
            $this->confirmarVigencia($registro);
        }

        $codigosExternos = collect($estaciones)->pluck('codigoExterno');
        $estacionesPorCodigo = Estacion::query()
            ->whereIn('est_codigo_externo', $codigosExternos)
            ->pluck('est_codigo', 'est_codigo_externo');
        $servicios = [];

        foreach ($horarios as $horario) {
            $codigoOrigen = $estacionesPorCodigo->get($horario->codigoEstacionOrigen);
            $codigoDestino = $estacionesPorCodigo->get($horario->codigoEstacionDestino);

            if ($codigoOrigen === null || $codigoDestino === null) {
                throw new UnexpectedValueException("El horario {$horario->codigoExterno} referencia una estación desconocida.");
            }

            $registro = Horario::updateOrCreate(
                ['hor_codigo_externo' => $horario->codigoExterno],
                [
                    'hor_est_codigo_origen' => $codigoOrigen,
                    'hor_est_codigo_destino' => $codigoDestino,
                    'hor_ser_codigo' => $servicios[$horario->servicio] ??= Servicio::query()->firstOrCreate(['ser_nombre' => $horario->servicio])->getKey(),
                    'hor_hora_salida' => $horario->horaSalida,
                    'hor_hora_llegada' => $horario->horaLlegada,
                    'hor_precio' => $horario->precio,
                    'hor_estado' => true,
                ],
            );
            $this->confirmarVigencia($registro);
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

            $registro = Clima::updateOrCreate(
                [
                    'cli_est_codigo' => $codigoEstacion,
                    // Se compara como fecha y hora para coincidir con el formato en que Eloquent guarda cli_fecha.
                    'cli_fecha' => Carbon::parse($pronostico->fecha)->startOfDay(),
                ],
                [
                    'cli_temperatura_minima' => $pronostico->temperaturaMinima,
                    'cli_temperatura_maxima' => $pronostico->temperaturaMaxima,
                    'cli_probabilidad_lluvia' => $pronostico->probabilidadLluvia,
                    'cli_descripcion' => $pronostico->descripcion,
                ],
            );
            $this->confirmarVigencia($registro);
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
            'bit_fue_codigo' => $fuente,
            'bit_tsi_codigo' => $tipo,
            'bit_fecha_inicio' => $inicio,
            'bit_fecha_fin' => now(),
            'bit_registros' => $registros,
            'bit_res_codigo' => ResultadoSincronizacion::Exito,
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
            'bit_fue_codigo' => $fuente,
            'bit_tsi_codigo' => $tipo,
            'bit_fecha_inicio' => $inicio,
            'bit_fecha_fin' => now(),
            'bit_registros' => 0,
            'bit_res_codigo' => ResultadoSincronizacion::Error,
            'bit_mensaje' => Str::limit($excepcion->getMessage(), 1000),
        ]);
    }

    /**
     * Clave del resultado que usan las pantallas para elegir el color del estado.
     */
    private function claveResultado(ResultadoSincronizacion $resultado): string
    {
        return $resultado === ResultadoSincronizacion::Exito ? 'exito' : 'error';
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
            ->where('bit_fue_codigo', $fuente->value)
            ->where('bit_res_codigo', ResultadoSincronizacion::Exito->value)
            ->max('bit_fecha_fin');

        return $ultimaEjecucion === null
            || Carbon::parse($ultimaEjecucion)->lte(now()->subHours($frecuencia));
    }

    /**
     * @return array{nombre: string, resultado: ?string, ultima_ejecucion: ?string, ultima_actualizacion: ?string, registros: int}
     */
    private function estadoFuente(FuenteDatos $fuente): array
    {
        $ultimoIntento = Bitacora::query()
            ->where('bit_fue_codigo', $fuente->value)
            ->latest('bit_fecha_fin')
            ->latest('bit_codigo')
            ->first();

        $ultimaActualizacion = Bitacora::query()
            ->where('bit_fue_codigo', $fuente->value)
            ->where('bit_res_codigo', ResultadoSincronizacion::Exito->value)
            ->max('bit_fecha_fin');

        return [
            'nombre' => $fuente->etiqueta(),
            'resultado' => $ultimoIntento === null ? null : $this->claveResultado($ultimoIntento->bit_res_codigo),
            'ultima_ejecucion' => $ultimoIntento?->bit_fecha_fin?->format('d/m/Y H:i'),
            'ultima_actualizacion' => $ultimaActualizacion === null
                ? null
                : Carbon::parse($ultimaActualizacion)->format('d/m/Y H:i'),
            'registros' => $ultimoIntento?->bit_registros ?? 0,
        ];
    }
}
