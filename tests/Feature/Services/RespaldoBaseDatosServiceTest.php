<?php

use App\Services\RespaldoBaseDatosService;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->archivoSqlite = tempnam(sys_get_temp_dir(), 'turismoapp-prueba-');
    file_put_contents($this->archivoSqlite, 'contenido de prueba');

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

test('genera un respaldo privado de una base sqlite', function () {
    $this->travelTo('2026-09-11 02:00:00');

    $ruta = app(RespaldoBaseDatosService::class)->generar();

    expect($ruta)->toBe('respaldos/turismoapp-2026-09-11_020000.sqlite');
    Storage::disk('local')->assertExists($ruta);
    expect(Storage::disk('local')->get($ruta))->toBe('contenido de prueba');
});

test('conserva únicamente las siete copias más recientes', function () {
    $inicio = now()->startOfDay();

    foreach (range(0, 7) as $dia) {
        $this->travelTo($inicio->copy()->addDays($dia));
        app(RespaldoBaseDatosService::class)->generar();
    }

    Storage::disk('local')->assertCount('respaldos', 7);
    Storage::disk('local')->assertMissing('respaldos/turismoapp-'.$inicio->format('Y-m-d_His').'.sqlite');
});

test('genera el respaldo mysql sin exponer la contraseña en el comando', function () {
    config([
        'database.connections.respaldo_prueba' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'turismo',
            'password' => 'clave-secreta',
            'database' => 'turismoapp',
        ],
    ]);
    Process::fake(function (PendingProcess $proceso) {
        $argumento = collect($proceso->command)
            ->first(fn (string $valor): bool => str_starts_with($valor, '--result-file='));
        file_put_contents(substr($argumento, strlen('--result-file=')), 'respaldo sql');

        return Process::result();
    });

    $ruta = app(RespaldoBaseDatosService::class)->generar();

    Storage::disk('local')->assertExists($ruta);
    expect(Storage::disk('local')->get($ruta))->toBe('respaldo sql');
    Process::assertRan(function (PendingProcess $proceso): bool {
        return is_array($proceso->command)
            && $proceso->command[0] === 'mysqldump'
            && ! str_contains(implode(' ', $proceso->command), 'clave-secreta')
            && $proceso->environment['MYSQL_PWD'] === 'clave-secreta';
    });
});

test('rechaza drivers que no tienen una estrategia segura de respaldo', function () {
    config([
        'database.connections.respaldo_prueba.driver' => 'sqlsrv',
    ]);

    expect(fn () => app(RespaldoBaseDatosService::class)->generar())
        ->toThrow(RuntimeException::class, 'no admite respaldos automáticos');
});
