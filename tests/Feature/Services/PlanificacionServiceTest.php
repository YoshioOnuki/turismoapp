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
        ->and($zonas[0]['distancia_total'])->toBe(1800)
        ->and($zonas[0]['tiempo_minutos'])->toBe(27);
});

test('lista los trenes de llegada y el pronóstico de la estación', function () {
    $estacion = Estacion::factory()->create();
    $origen = Estacion::factory()->create(['est_nombre' => 'Estación Origen']);
    Horario::factory()->create([
        'hor_est_codigo_origen' => $origen->getKey(),
        'hor_est_codigo_destino' => $estacion->getKey(),
        'hor_servicio' => 'Vistadome',
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

    expect($informacion['trenes'])->toHaveCount(1)
        ->and($informacion['trenes'][0]['origen'])->toBe('Estación Origen')
        ->and($informacion['trenes'][0]['llegada'])->toBe('10:30');
    expect($informacion['clima'])->toHaveCount(1)
        ->and($informacion['clima'][0]['descripcion'])->toBe('Cielo despejado');
});
