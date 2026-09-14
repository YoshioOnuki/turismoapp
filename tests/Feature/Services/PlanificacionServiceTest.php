<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Clima;
use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Parametro;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use App\Services\PlanificacionService;
use Illuminate\Support\Carbon;

test('filtra zonas activas por estación preferencias y distancia máxima', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create();
    $preferida = Categoria::factory()->create();
    $otra = Categoria::factory()->create();
    $usuario->preferencias()->attach($preferida);
    Parametro::factory()->create(['par_clave' => 'distancia_maxima_caminable', 'par_valor' => '1000']);
    Parametro::factory()->create(['par_clave' => 'velocidad_caminata', 'par_valor' => '4']);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $preferida->getKey(),
        'zon_nombre' => 'Zona disponible',
        'zon_latitud' => -13.1605,
        'zon_longitud' => -72.5421,
        'zon_distancia' => 900,
    ]);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $preferida->getKey(),
        'zon_nombre' => 'Zona lejana',
        'zon_distancia' => 1100,
    ]);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $otra->getKey(),
        'zon_nombre' => 'Otra categoría',
        'zon_distancia' => 500,
    ]);

    $zonas = app(PlanificacionService::class)->zonasDisponibles($usuario, $estacion->getKey());

    expect($zonas)->toHaveCount(1)
        ->and($zonas[0]['nombre'])->toBe('Zona disponible')
        ->and($zonas[0]['latitud'])->toBe(-13.1605)
        ->and($zonas[0]['longitud'])->toBe(-72.5421)
        ->and($zonas[0]['distancia_total'])->toBe(1800)
        ->and($zonas[0]['tiempo_minutos'])->toBe(27);
});

test('lista los trenes de llegada y el pronóstico de la estación', function () {
    $estacion = Estacion::factory()->create([
        'est_nombre' => 'Estación del Valle',
        'est_latitud' => -13.2587,
        'est_longitud' => -72.2636,
    ]);
    $origen = Estacion::factory()->create(['est_nombre' => 'Estación Origen']);
    Horario::factory()->conServicio('Vistadome')->create([
        'hor_est_codigo_origen' => $origen->getKey(),
        'hor_est_codigo_destino' => $estacion->getKey(),
        'hor_hora_salida' => '08:00:00',
        'hor_hora_llegada' => '10:30:00',
        'hor_precio' => 250,
    ]);
    Clima::factory()->create([
        'cli_est_codigo' => $estacion->getKey(),
        'cli_fecha' => today(),
        'cli_descripcion' => 'Cielo despejado',
    ]);

    $informacion = app(PlanificacionService::class)->informacionEstacion($estacion->getKey());

    expect($informacion['estacion'])->toBe([
        'codigo' => $estacion->getKey(),
        'nombre' => 'Estación del Valle',
        'latitud' => -13.2587,
        'longitud' => -72.2636,
    ]);
    expect($informacion['trenes'])->toHaveCount(1)
        ->and($informacion['trenes'][0]['origen'])->toBe('Estación Origen')
        ->and($informacion['trenes'][0]['llegada'])->toBe('10:30')
        ->and($informacion['trenes'][0]['duracion'])->toBe('2 h 30 min')
        ->and($informacion['actualizacion']['trenes'])->not->toBeNull();
    expect($informacion['clima'])->toHaveCount(1)
        ->and($informacion['clima'][0]['descripcion'])->toBe('Cielo despejado')
        ->and($informacion['clima_vigente'])->toBeTrue();
});

test('entrega el último pronóstico disponible con su fecha de actualización si no hay uno vigente', function () {
    $this->travelTo(Carbon::parse('2026-09-11 08:00:00'));
    $estacion = Estacion::factory()->create();
    Clima::factory()->create([
        'cli_est_codigo' => $estacion->getKey(),
        'cli_fecha' => today()->subDays(2),
        'cli_descripcion' => 'Pronóstico antiguo',
    ]);
    Clima::factory()->create([
        'cli_est_codigo' => $estacion->getKey(),
        'cli_fecha' => today()->subDay(),
        'cli_descripcion' => 'Último pronóstico',
    ]);
    $this->travelTo(Carbon::parse('2026-09-11 10:30:00'));

    $informacion = app(PlanificacionService::class)->informacionEstacion($estacion->getKey());

    expect($informacion['clima_vigente'])->toBeFalse()
        ->and($informacion['clima'])->toHaveCount(2)
        ->and($informacion['clima'][1]['descripcion'])->toBe('Último pronóstico')
        ->and($informacion['actualizacion']['clima'])->toBe('11/09/2026 08:00');
});
