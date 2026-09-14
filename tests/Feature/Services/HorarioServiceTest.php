<?php

use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Servicio;
use App\Services\HorarioService;

test('registra un horario activo con un código propio distinto al de PeruRail', function () {
    $origen = Estacion::factory()->create();
    $destino = Estacion::factory()->create();

    $horario = app(HorarioService::class)->guardar(null, [
        'hor_est_codigo_origen' => $origen->getKey(),
        'hor_est_codigo_destino' => $destino->getKey(),
        'hor_hora_salida' => '06:10:00',
        'hor_hora_llegada' => '07:40:00',
        'hor_precio' => 250.0,
    ], 'Expedition');

    expect($horario->hor_codigo_externo)->toStartWith('MTC-')
        ->and($horario->hor_estado)->toBeTrue()
        ->and($horario->servicio->ser_nombre)->toBe('Expedition')
        ->and($horario->duracionFormateada())->toBe('1 h 30 min');
});

test('reutiliza el servicio del catálogo en lugar de repetir su nombre', function () {
    $servicio = Servicio::factory()->create(['ser_nombre' => 'Vistadome']);
    $horario = Horario::factory()->create();

    app(HorarioService::class)->guardar($horario->getKey(), [
        'hor_est_codigo_origen' => $horario->hor_est_codigo_origen,
        'hor_est_codigo_destino' => $horario->hor_est_codigo_destino,
        'hor_hora_salida' => '07:05:00',
        'hor_hora_llegada' => '08:35:00',
        'hor_precio' => 310.0,
    ], 'Vistadome');

    expect($horario->fresh()->hor_ser_codigo)->toBe($servicio->getKey());
    $this->assertDatabaseCount('tb_servicio', 2);
});

test('lista los horarios con su ruta, tiempo de viaje y precio', function () {
    $origen = Estacion::factory()->create(['est_nombre' => 'Estación Poroy']);
    $destino = Estacion::factory()->create(['est_nombre' => 'Estación Machu Picchu']);
    Horario::factory()->conServicio('Vistadome')->create([
        'hor_est_codigo_origen' => $origen->getKey(),
        'hor_est_codigo_destino' => $destino->getKey(),
        'hor_hora_salida' => '06:40:00',
        'hor_hora_llegada' => '10:12:00',
        'hor_precio' => 420,
    ]);

    $horarios = app(HorarioService::class)->listar();

    expect($horarios)->toHaveCount(1)
        ->and($horarios[0])->toMatchArray([
            'origen' => 'Estación Poroy',
            'destino' => 'Estación Machu Picchu',
            'servicio' => 'Vistadome',
            'salida' => '06:40',
            'llegada' => '10:12',
            'duracion' => '3 h 32 min',
            'precio' => '420.00',
            'estado' => true,
        ]);
});

test('cambia el estado de un horario sin eliminarlo', function () {
    $horario = Horario::factory()->create();
    $servicio = app(HorarioService::class);

    expect($servicio->cambiarEstado($horario->getKey())->hor_estado)->toBeFalse()
        ->and($servicio->cambiarEstado($horario->getKey())->hor_estado)->toBeTrue();
    $this->assertDatabaseCount('tb_horario', 1);
});
