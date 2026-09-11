<?php

use App\Services\FuenteSenamhi;
use App\Services\FuenteSenamhiSimulada;
use App\Services\PronosticoExterno;
use Illuminate\Support\Carbon;

test('resuelve la fuente simulada desde el contrato del SENAMHI', function () {
    $fuente = app(FuenteSenamhi::class);

    expect($fuente)->toBeInstanceOf(FuenteSenamhiSimulada::class);
});

test('entrega tres pronósticos diarios por cada estación', function () {
    $this->travelTo(Carbon::parse('2026-09-11 08:00:00'));

    $pronosticos = app(FuenteSenamhi::class)->obtenerPronosticos();

    expect($pronosticos)->toHaveCount(15);
    expect($pronosticos[0])->toEqual(new PronosticoExterno(
        codigoEstacion: 'WAN',
        fecha: '2026-09-11',
        temperaturaMinima: 7.0,
        temperaturaMaxima: 19.0,
        probabilidadLluvia: 30,
        descripcion: 'Parcialmente nublado',
    ));
    expect(collect($pronosticos)->countBy('codigoEstacion')->all())->toBe([
        'WAN' => 3,
        'POR' => 3,
        'OLL' => 3,
        'MAP' => 3,
        'PUN' => 3,
    ]);
    expect(collect($pronosticos)->map(
        fn (PronosticoExterno $pronostico) => $pronostico->codigoEstacion.'|'.$pronostico->fecha,
    )->unique())->toHaveCount(15);
});
