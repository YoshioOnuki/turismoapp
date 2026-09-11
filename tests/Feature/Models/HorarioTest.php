<?php

use App\Models\Horario;

test('calcula el tiempo de viaje en minutos', function () {
    $horario = new Horario(['hor_hora_salida' => '06:10:00', 'hor_hora_llegada' => '07:40:00']);

    expect($horario->duracionEnMinutos())->toBe(90);
});

test('suma un día cuando el tren llega después de la medianoche', function () {
    $horario = new Horario(['hor_hora_salida' => '22:30:00', 'hor_hora_llegada' => '01:15:00']);

    expect($horario->duracionEnMinutos())->toBe(165);
});
