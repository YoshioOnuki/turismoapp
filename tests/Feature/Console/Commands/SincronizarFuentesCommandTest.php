<?php

use App\Enums\FuenteDatos;
use App\Enums\ResultadoSincronizacion;
use App\Models\Bitacora;
use App\Models\Parametro;
use Illuminate\Console\Scheduling\Schedule;

test('ejecuta las fuentes pendientes e informa los registros procesados', function () {
    Parametro::factory()->create([
        'par_clave' => 'frecuencia_sincronizacion',
        'par_valor' => '24',
    ]);

    $this->artisan('sincronizacion:ejecutar')
        ->expectsOutputToContain('PeruRail: 14 registros sincronizados.')
        ->expectsOutputToContain('SENAMHI: 15 registros sincronizados.')
        ->assertSuccessful();

    $this->assertDatabaseCount('tb_bitacora', 2);
});

test('finaliza sin consultar fuentes cuando los datos siguen vigentes', function () {
    Parametro::factory()->create([
        'par_clave' => 'frecuencia_sincronizacion',
        'par_valor' => '24',
    ]);
    foreach ([FuenteDatos::PeruRail, FuenteDatos::Senamhi] as $fuente) {
        Bitacora::factory()->create([
            'bit_fue_codigo' => $fuente,
            'bit_res_codigo' => ResultadoSincronizacion::Exito,
            'bit_fecha_fin' => now(),
        ]);
    }

    $this->artisan('sincronizacion:ejecutar')
        ->expectsOutput('No hay fuentes pendientes de sincronización.')
        ->assertSuccessful();

    $this->assertDatabaseCount('tb_bitacora', 2);
});

test('registra el comando cada hora sin permitir ejecuciones superpuestas', function () {
    $evento = collect(app(Schedule::class)->events())
        ->first(fn ($evento) => str_contains($evento->command, 'sincronizacion:ejecutar'));

    expect($evento)->not->toBeNull();
    expect($evento->expression)->toBe('0 * * * *');
    expect($evento->withoutOverlapping)->toBeTrue();
});
