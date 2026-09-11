<?php

use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Informe;
use App\Models\ZonaTuristica;
use App\Services\ReporteService;

test('resume únicamente las zonas activas por estación', function () {
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación Central']);
    ZonaTuristica::factory()->create(['zon_est_codigo' => $estacion->getKey(), 'zon_nombre' => 'Zona activa']);
    ZonaTuristica::factory()->create(['zon_est_codigo' => $estacion->getKey(), 'zon_nombre' => 'Zona inactiva', 'zon_estado' => false]);

    $reporte = app(ReporteService::class)->zonasPorEstacion();

    expect($reporte)->toHaveCount(1)
        ->and($reporte[0]['zonas'])->toBe('Zona activa')
        ->and($reporte[0]['total'])->toBe(1);
});

test('ordena estaciones y categorías por cantidad de consultas', function () {
    $primera = Estacion::factory()->create(['est_nombre' => 'Primera']);
    $segunda = Estacion::factory()->create(['est_nombre' => 'Segunda']);
    $categoria = Categoria::factory()->create(['cat_nombre' => 'Naturaleza']);
    Informe::factory()->create(['inf_est_codigo' => $primera->getKey()]);
    $informe = Informe::factory()->create(['inf_est_codigo' => $segunda->getKey()]);
    Informe::factory()->create(['inf_est_codigo' => $segunda->getKey()]);
    $informe->categorias()->attach($categoria);

    $reporte = app(ReporteService::class)->uso();

    expect($reporte['estaciones'][0]['nombre'])->toBe('Segunda')
        ->and($reporte['estaciones'][0]['consultas'])->toBe(2);
    expect($reporte['categorias'][0]['nombre'])->toBe('Naturaleza')
        ->and($reporte['categorias'][0]['consultas'])->toBe(1);
});
