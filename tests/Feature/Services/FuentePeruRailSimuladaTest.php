<?php

use App\Services\EstacionExterna;
use App\Services\FuentePeruRail;
use App\Services\FuentePeruRailSimulada;
use App\Services\HorarioExterno;

test('resuelve la fuente simulada desde el contrato de PeruRail', function () {
    $fuente = app(FuentePeruRail::class);

    expect($fuente)->toBeInstanceOf(FuentePeruRailSimulada::class);
});

test('entrega estaciones con códigos externos únicos', function () {
    $estaciones = app(FuentePeruRail::class)->obtenerEstaciones();

    expect($estaciones)->toHaveCount(5);
    expect($estaciones[0])->toEqual(new EstacionExterna(
        codigoExterno: 'WAN',
        nombre: 'Estación Wanchaq (Cusco)',
        latitud: -13.5236,
        longitud: -71.9669,
    ));
    expect(collect($estaciones)->pluck('codigoExterno')->unique())->toHaveCount(5);
});

test('entrega horarios cuyas estaciones existen en la misma fuente', function () {
    $fuente = app(FuentePeruRail::class);
    $codigosEstacion = collect($fuente->obtenerEstaciones())->pluck('codigoExterno');

    $horarios = $fuente->obtenerHorarios();

    expect($horarios)->toHaveCount(9);
    expect($horarios[0])->toEqual(new HorarioExterno(
        codigoExterno: 'DEMO-31',
        codigoEstacionOrigen: 'POR',
        codigoEstacionDestino: 'MAP',
        servicio: 'Vistadome',
        horaSalida: '06:40:00',
        horaLlegada: '10:12:00',
        precio: 420.00,
    ));
    expect(collect($horarios)->pluck('codigoExterno')->unique())->toHaveCount(9);
    expect(collect($horarios)->every(
        fn (HorarioExterno $horario) => $codigosEstacion->contains($horario->codigoEstacionOrigen)
            && $codigosEstacion->contains($horario->codigoEstacionDestino),
    ))->toBeTrue();
});
