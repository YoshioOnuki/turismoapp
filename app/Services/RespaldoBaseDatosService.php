<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class RespaldoBaseDatosService
{
    public function generar(): string
    {
        $conexionNombre = (string) (config('respaldos.conexion') ?: config('database.default'));
        $conexion = config("database.connections.{$conexionNombre}");

        if (! is_array($conexion)) {
            throw new RuntimeException('La conexión de base de datos no está configurada.');
        }

        $driver = (string) ($conexion['driver'] ?? '');
        $extension = $driver === 'sqlite' ? 'sqlite' : 'sql';
        $ruta = $this->rutaDestino($extension);
        $disco = Storage::disk((string) config('respaldos.disco', 'local'));

        match ($driver) {
            'sqlite' => $this->respaldarSqlite($conexion, $disco, $ruta),
            'mysql', 'mariadb' => $this->respaldarMysql($conexion, $disco, $ruta),
            default => throw new RuntimeException("El driver {$driver} no admite respaldos automáticos."),
        };

        $this->eliminarCopiasAntiguas($disco);

        return $ruta;
    }

    private function rutaDestino(string $extension): string
    {
        $directorio = trim(str_replace('\\', '/', (string) config('respaldos.directorio', 'respaldos')), '/');

        if ($directorio === '' || str_contains($directorio, '..')) {
            throw new RuntimeException('El directorio de respaldos no es válido.');
        }

        $aplicacion = Str::slug((string) config('app.name', 'turismoapp')) ?: 'turismoapp';

        return sprintf('%s/%s-%s.%s', $directorio, $aplicacion, now()->format('Y-m-d_His'), $extension);
    }

    /** @param array<string, mixed> $conexion */
    private function respaldarSqlite(array $conexion, FilesystemAdapter $disco, string $ruta): void
    {
        $origen = (string) ($conexion['database'] ?? '');

        if ($origen === ':memory:' || ! is_file($origen)) {
            throw new RuntimeException('El archivo SQLite que se debe respaldar no existe.');
        }

        $this->guardarArchivo($disco, $ruta, $origen);
    }

    /** @param array<string, mixed> $conexion */
    private function respaldarMysql(array $conexion, FilesystemAdapter $disco, string $ruta): void
    {
        $configuracion = $this->configuracionMysql($conexion);

        if ($configuracion['database'] === '') {
            throw new RuntimeException('La base de datos MySQL que se debe respaldar no está configurada.');
        }

        $temporal = tempnam(sys_get_temp_dir(), 'turismoapp-respaldo-');

        if ($temporal === false) {
            throw new RuntimeException('No se pudo crear el archivo temporal del respaldo.');
        }

        try {
            $resultado = Process::timeout((int) config('respaldos.tiempo_limite', 300))
                ->env(['MYSQL_PWD' => $configuracion['password']])
                ->run([
                    (string) config('respaldos.mysqldump', 'mysqldump'),
                    '--host='.$configuracion['host'],
                    '--port='.$configuracion['port'],
                    '--user='.$configuracion['username'],
                    '--single-transaction',
                    '--quick',
                    '--no-tablespaces',
                    '--default-character-set=utf8mb4',
                    '--routines',
                    '--triggers',
                    '--result-file='.$temporal,
                    $configuracion['database'],
                ]);

            if ($resultado->failed()) {
                throw new RuntimeException('mysqldump no pudo generar el respaldo: '.$resultado->errorOutput());
            }

            $this->guardarArchivo($disco, $ruta, $temporal);
        } finally {
            if (is_file($temporal)) {
                unlink($temporal);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $conexion
     * @return array{host: string, port: string, username: string, password: string, database: string}
     */
    private function configuracionMysql(array $conexion): array
    {
        $url = $conexion['url'] ?? null;

        if (is_string($url) && $url !== '') {
            $partes = parse_url($url);

            if ($partes === false) {
                throw new RuntimeException('La URL de conexión de MySQL no es válida.');
            }

            return [
                'host' => (string) ($partes['host'] ?? '127.0.0.1'),
                'port' => (string) ($partes['port'] ?? 3306),
                'username' => rawurldecode((string) ($partes['user'] ?? 'root')),
                'password' => rawurldecode((string) ($partes['pass'] ?? '')),
                'database' => ltrim(rawurldecode((string) ($partes['path'] ?? '')), '/'),
            ];
        }

        return [
            'host' => (string) ($conexion['host'] ?? '127.0.0.1'),
            'port' => (string) ($conexion['port'] ?? 3306),
            'username' => (string) ($conexion['username'] ?? 'root'),
            'password' => (string) ($conexion['password'] ?? ''),
            'database' => (string) ($conexion['database'] ?? ''),
        ];
    }

    private function guardarArchivo(FilesystemAdapter $disco, string $ruta, string $origen): void
    {
        $flujo = fopen($origen, 'rb');

        if ($flujo === false) {
            throw new RuntimeException('No se pudo leer el respaldo generado.');
        }

        try {
            if (! $disco->put($ruta, $flujo)) {
                throw new RuntimeException('No se pudo guardar el respaldo en el disco configurado.');
            }
        } finally {
            fclose($flujo);
        }
    }

    private function eliminarCopiasAntiguas(FilesystemAdapter $disco): void
    {
        $directorio = trim(str_replace('\\', '/', (string) config('respaldos.directorio', 'respaldos')), '/');
        $copias = max(1, (int) config('respaldos.copias', 7));
        $archivos = collect($disco->files($directorio))
            ->filter(fn (string $archivo): bool => str_ends_with($archivo, '.sql') || str_ends_with($archivo, '.sqlite'))
            ->sortDesc()
            ->values()
            ->slice($copias)
            ->all();

        if ($archivos !== []) {
            $disco->delete($archivos);
        }
    }
}
