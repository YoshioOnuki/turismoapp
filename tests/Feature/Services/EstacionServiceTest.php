<?php

use App\Models\Estacion;
use App\Models\ZonaTuristica;
use App\Services\EstacionService;

test('lista estaciones con la cantidad de zonas activas', function () {
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación de prueba']);
    ZonaTuristica::factory()->count(2)->create(['zon_est_codigo' => $estacion->getKey()]);
    ZonaTuristica::factory()->create(['zon_est_codigo' => $estacion->getKey(), 'zon_estado' => false]);

    $resultado = app(EstacionService::class)->listar();

    expect($resultado)->toHaveCount(1)
        ->and($resultado[0]['nombre'])->toBe('Estación de prueba')
        ->and($resultado[0]['zonas_activas'])->toBe(2);
});
