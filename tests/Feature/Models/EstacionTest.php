<?php

use App\Models\Estacion;
use App\Models\Horario;
use App\Models\ZonaTuristica;

test('lista solo los trenes que llegan a la estación', function () {
    $machuPicchu = Estacion::factory()->create();
    $ollantaytambo = Estacion::factory()->create();
    $trenDeLlegada = Horario::factory()
        ->for($ollantaytambo, 'estacionOrigen')
        ->for($machuPicchu, 'estacionDestino')
        ->create();
    Horario::factory()
        ->for($machuPicchu, 'estacionOrigen')
        ->for($ollantaytambo, 'estacionDestino')
        ->create();

    $trenes = $machuPicchu->horariosDeLlegada;

    expect($trenes->modelKeys())->toBe([$trenDeLlegada->hor_codigo]);
});

test('lista solo las zonas turísticas de la estación', function () {
    $estacion = Estacion::factory()->create();
    $zona = ZonaTuristica::factory()->for($estacion)->create();
    ZonaTuristica::factory()->create();

    $zonas = $estacion->zonasTuristicas;

    expect($zonas->modelKeys())->toBe([$zona->zon_codigo]);
});
