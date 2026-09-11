<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->archivoSqlite = tempnam(sys_get_temp_dir(), 'turismoapp-comando-');
    file_put_contents($this->archivoSqlite, 'base de prueba');

    config([
        'app.name' => 'TurismoApp',
        'database.connections.respaldo_prueba' => [
            'driver' => 'sqlite',
            'database' => $this->archivoSqlite,
        ],
        'respaldos.conexion' => 'respaldo_prueba',
        'respaldos.disco' => 'local',
        'respaldos.directorio' => 'respaldos',
        'respaldos.copias' => 7,
    ]);
});

afterEach(function () {
    if (is_string($this->archivoSqlite) && is_file($this->archivoSqlite)) {
        unlink($this->archivoSqlite);
    }
});

test('el comando genera un respaldo de la base de datos', function () {
    $this->artisan('respaldo:base-datos')
        ->expectsOutputToContain('Respaldo creado: respaldos/')
        ->assertSuccessful();

    Storage::disk('local')->assertCount('respaldos', 1);
});

test('programa un respaldo diario sin ejecuciones superpuestas', function () {
    $evento = collect(app(Schedule::class)->events())
        ->first(fn ($evento) => str_contains($evento->command, 'respaldo:base-datos'));

    expect($evento)->not->toBeNull();
    expect($evento->expression)->toBe('0 2 * * *');
    expect($evento->withoutOverlapping)->toBeTrue();
});
