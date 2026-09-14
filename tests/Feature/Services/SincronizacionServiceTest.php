<?php

use App\Enums\FuenteDatos;
use App\Enums\ResultadoSincronizacion;
use App\Enums\TipoPerfil;
use App\Enums\TipoSincronizacion;
use App\Models\Bitacora;
use App\Models\Clima;
use App\Models\Estacion;
use App\Models\Parametro;
use App\Models\Usuario;
use App\Services\EstacionExterna;
use App\Services\FuentePeruRail;
use App\Services\FuenteSenamhi;
use App\Services\HorarioExterno;
use App\Services\PronosticoExterno;
use App\Services\SincronizacionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Exceptions;

test('sincroniza ambas fuentes y registra la ejecución manual del administrador', function () {
    $this->travelTo(Carbon::parse('2026-09-11 08:00:00'));
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();

    $resultados = app(SincronizacionService::class)->ejecutar(TipoSincronizacion::Manual, $administrador);

    expect($resultados)->toHaveKeys([FuenteDatos::PeruRail->value, FuenteDatos::Senamhi->value]);
    expect($resultados[FuenteDatos::PeruRail->value]->bit_res_codigo)->toBe(ResultadoSincronizacion::Exito);
    expect($resultados[FuenteDatos::Senamhi->value]->bit_res_codigo)->toBe(ResultadoSincronizacion::Exito);
    $this->assertDatabaseCount('tb_estacion', 5);
    $this->assertDatabaseCount('tb_horario', 9);
    $this->assertDatabaseCount('tb_servicio', 3);
    $this->assertDatabaseCount('tb_clima', 15);
    $this->assertDatabaseHas('tb_bitacora', [
        'bit_usu_codigo' => $administrador->getKey(),
        'bit_fue_codigo' => FuenteDatos::PeruRail->value,
        'bit_tsi_codigo' => TipoSincronizacion::Manual->value,
        'bit_registros' => 14,
        'bit_res_codigo' => ResultadoSincronizacion::Exito->value,
    ]);
    $this->assertDatabaseHas('tb_bitacora', [
        'bit_usu_codigo' => $administrador->getKey(),
        'bit_fue_codigo' => FuenteDatos::Senamhi->value,
        'bit_tsi_codigo' => TipoSincronizacion::Manual->value,
        'bit_registros' => 15,
        'bit_res_codigo' => ResultadoSincronizacion::Exito->value,
    ]);
});

test('actualiza los registros existentes sin crear duplicados', function () {
    $this->travelTo(Carbon::parse('2026-09-11 08:00:00'));
    $sincronizacion = app(SincronizacionService::class);
    $sincronizacion->ejecutar(TipoSincronizacion::Automatica);
    Estacion::where('est_codigo_externo', 'WAN')->update(['est_nombre' => 'Nombre desactualizado']);

    $sincronizacion->ejecutar(TipoSincronizacion::Automatica);

    $this->assertDatabaseCount('tb_estacion', 5);
    $this->assertDatabaseCount('tb_horario', 9);
    $this->assertDatabaseCount('tb_clima', 15);
    $this->assertDatabaseHas('tb_estacion', [
        'est_codigo_externo' => 'WAN',
        'est_nombre' => 'Estación Wanchaq (Cusco)',
    ]);
    $this->assertDatabaseCount('tb_bitacora', 4);
    $this->assertDatabaseMissing('tb_bitacora', ['bit_res_codigo' => ResultadoSincronizacion::Error->value]);
});

test('actualiza el pronóstico registrado previamente para la misma estación y fecha', function () {
    $this->travelTo(Carbon::parse('2026-09-11 08:00:00'));
    $estacion = Estacion::factory()->create(['est_codigo_externo' => 'WAN']);
    Clima::factory()->for($estacion)->create([
        'cli_fecha' => today(),
        'cli_descripcion' => 'Pronóstico anterior',
    ]);
    $this->mock(FuenteSenamhi::class)
        ->shouldReceive('obtenerPronosticos')
        ->once()
        ->andReturn([new PronosticoExterno('WAN', today()->toDateString(), 7.0, 19.0, 30, 'Nublado')]);

    $resultados = app(SincronizacionService::class)->ejecutar(TipoSincronizacion::Automatica);

    expect($resultados[FuenteDatos::Senamhi->value]->bit_res_codigo)->toBe(ResultadoSincronizacion::Exito);
    $this->assertDatabaseCount('tb_clima', 1);
    $this->assertDatabaseHas('tb_clima', ['cli_est_codigo' => $estacion->getKey(), 'cli_descripcion' => 'Nublado']);
});

test('conserva los datos previos y registra el error cuando una fuente falla', function () {
    $estacion = Estacion::factory()->create([
        'est_codigo_externo' => 'WAN',
        'est_nombre' => 'Dato previo conservado',
    ]);
    Exceptions::fake();
    $this->mock(FuentePeruRail::class)
        ->shouldReceive('obtenerEstaciones')
        ->once()
        ->andReturn([new EstacionExterna('WAN', 'Nombre nuevo', -13.5, -71.9)])
        ->shouldReceive('obtenerHorarios')
        ->once()
        ->andReturn([new HorarioExterno('TREN-1', 'WAN', 'DESCONOCIDA', 'Expedition', '08:00:00', '09:00:00', 100.00)]);
    $this->mock(FuenteSenamhi::class)
        ->shouldReceive('obtenerPronosticos')
        ->once()
        ->andReturn([new PronosticoExterno('WAN', today()->toDateString(), 7.0, 19.0, 30, 'Nublado')]);

    $resultados = app(SincronizacionService::class)->ejecutar(TipoSincronizacion::Automatica);

    expect($estacion->fresh()->est_nombre)->toBe('Dato previo conservado');
    expect($resultados[FuenteDatos::PeruRail->value]->bit_res_codigo)->toBe(ResultadoSincronizacion::Error);
    expect($resultados[FuenteDatos::Senamhi->value]->bit_res_codigo)->toBe(ResultadoSincronizacion::Exito);
    $this->assertDatabaseHas('tb_bitacora', [
        'bit_fue_codigo' => FuenteDatos::PeruRail->value,
        'bit_tsi_codigo' => TipoSincronizacion::Automatica->value,
        'bit_registros' => 0,
        'bit_res_codigo' => ResultadoSincronizacion::Error->value,
        'bit_mensaje' => 'El horario TREN-1 referencia una estación desconocida.',
    ]);
    $this->assertDatabaseHas('tb_clima', ['cli_est_codigo' => $estacion->getKey()]);
    Exceptions::assertReported(UnexpectedValueException::class);
});

test('sincroniza automáticamente solo la fuente cuyos datos perdieron vigencia', function () {
    $this->travelTo(Carbon::parse('2026-09-11 08:00:00'));
    Parametro::factory()->create([
        'par_clave' => 'frecuencia_sincronizacion',
        'par_valor' => '24',
    ]);
    Bitacora::factory()->create([
        'bit_fue_codigo' => FuenteDatos::PeruRail,
        'bit_res_codigo' => ResultadoSincronizacion::Exito,
        'bit_fecha_inicio' => now()->subHours(23)->subMinute(),
        'bit_fecha_fin' => now()->subHours(23),
    ]);
    $estacion = Estacion::factory()->create(['est_codigo_externo' => 'WAN']);
    $this->mock(FuentePeruRail::class)
        ->shouldNotReceive('obtenerEstaciones', 'obtenerHorarios');
    $this->mock(FuenteSenamhi::class)
        ->shouldReceive('obtenerPronosticos')
        ->once()
        ->andReturn([new PronosticoExterno('WAN', today()->toDateString(), 7.0, 19.0, 30, 'Nublado')]);

    $resultados = app(SincronizacionService::class)->ejecutarAutomaticamenteSiCorresponde();

    expect($resultados)->toHaveKey(FuenteDatos::Senamhi->value)
        ->not->toHaveKey(FuenteDatos::PeruRail->value);
    $this->assertDatabaseHas('tb_clima', ['cli_est_codigo' => $estacion->getKey()]);
});

test('no sincroniza automáticamente mientras ambas fuentes conservan vigencia', function () {
    $this->travelTo(Carbon::parse('2026-09-11 08:00:00'));
    Parametro::factory()->create([
        'par_clave' => 'frecuencia_sincronizacion',
        'par_valor' => '24',
    ]);
    foreach ([FuenteDatos::PeruRail, FuenteDatos::Senamhi] as $fuente) {
        Bitacora::factory()->create([
            'bit_fue_codigo' => $fuente,
            'bit_res_codigo' => ResultadoSincronizacion::Exito,
            'bit_fecha_inicio' => now()->subHours(23)->subMinute(),
            'bit_fecha_fin' => now()->subHours(23),
        ]);
    }
    $this->mock(FuentePeruRail::class)
        ->shouldNotReceive('obtenerEstaciones', 'obtenerHorarios');
    $this->mock(FuenteSenamhi::class)
        ->shouldNotReceive('obtenerPronosticos');

    $resultados = app(SincronizacionService::class)->ejecutarAutomaticamenteSiCorresponde();

    expect($resultados)->toBe([]);
    $this->assertDatabaseCount('tb_bitacora', 2);
});

test('renueva la fecha de actualización de los datos que la fuente vuelve a confirmar', function () {
    $this->travelTo(Carbon::parse('2026-09-11 08:00:00'));
    $sincronizacion = app(SincronizacionService::class);
    $sincronizacion->ejecutar(TipoSincronizacion::Automatica);
    $this->travelTo(Carbon::parse('2026-09-11 20:00:00'));

    $sincronizacion->ejecutar(TipoSincronizacion::Automatica);

    expect(Estacion::where('est_codigo_externo', 'WAN')->sole()->est_fecha_actualizacion->format('Y-m-d H:i'))
        ->toBe('2026-09-11 20:00');
});

test('lista la bitácora de la ejecución más reciente a la más antigua', function () {
    $this->travelTo(Carbon::parse('2026-09-11 08:00:00'));
    Bitacora::factory()->create([
        'bit_fue_codigo' => FuenteDatos::PeruRail,
        'bit_fecha_inicio' => now()->subDay()->subMinute(),
        'bit_fecha_fin' => now()->subDay(),
    ]);
    Bitacora::factory()->create([
        'bit_fue_codigo' => FuenteDatos::TravelGroup,
        'bit_tsi_codigo' => TipoSincronizacion::Manual,
        'bit_fecha_inicio' => now()->subMinute(),
        'bit_fecha_fin' => now(),
    ]);

    $bitacora = app(SincronizacionService::class)->bitacoraReciente();

    expect($bitacora)->toHaveCount(2)
        ->and($bitacora[0]['fuente'])->toBe('Travel Group Perú')
        ->and($bitacora[0]['tipo'])->toBe('Manual')
        ->and($bitacora[1]['fuente'])->toBe('PeruRail')
        ->and($bitacora[1]['fecha'])->toBe('10/09/2026 08:00');
});
