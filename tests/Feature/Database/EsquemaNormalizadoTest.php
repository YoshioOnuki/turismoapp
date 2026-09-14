<?php

use App\Models\Categoria;
use App\Models\Clima;
use App\Models\Estacion;
use App\Models\Horario;
use App\Models\ZonaTuristica;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prefijo de campos de cada tabla del dominio (RNF-15).
 *
 * @return array<string, string>
 */
function prefijosDeTablas(): array
{
    return [
        'tb_bitacora' => 'bit',
        'tb_categoria' => 'cat',
        'tb_clima' => 'cli',
        'tb_dificultad' => 'dif',
        'tb_estacion' => 'est',
        'tb_fuente' => 'fue',
        'tb_horario' => 'hor',
        'tb_informe' => 'inf',
        'tb_informe_categoria' => 'ica',
        'tb_informe_clima' => 'icl',
        'tb_informe_horario' => 'iho',
        'tb_informe_zona' => 'izo',
        'tb_parametro' => 'par',
        'tb_perfil' => 'per',
        'tb_preferencia' => 'pre',
        'tb_resultado_sincronizacion' => 'res',
        'tb_servicio' => 'ser',
        'tb_tipo_sincronizacion' => 'tsi',
        'tb_usuario' => 'usu',
        'tb_zona_imagen' => 'zim',
        'tb_zona_turistica' => 'zon',
    ];
}

function usaMysql(): bool
{
    return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
}

test('solo quedan las tablas del dominio y las tablas técnicas de Laravel', function () {
    $tablas = Schema::getTableListing(Schema::getCurrentSchemaListing(), schemaQualified: false);
    $tecnicas = ['cache', 'cache_locks', 'migrations', 'password_reset_tokens', 'sessions'];

    expect($tablas)->toEqualCanonicalizing([...array_keys(prefijosDeTablas()), ...$tecnicas]);
});

test('cada campo usa el prefijo de su tabla y la llave primaria se llama prefijo_codigo', function (string $tabla, string $prefijo) {
    $columnas = Schema::getColumnListing($tabla);
    $primaria = collect(Schema::getIndexes($tabla))->firstWhere('primary', true);

    expect($columnas)->each->toStartWith("{$prefijo}_")
        ->and($primaria['columns'])->toBe(["{$prefijo}_codigo"]);
})->with(fn (): array => collect(prefijosDeTablas())->map(fn (string $prefijo, string $tabla): array => [$tabla, $prefijo])->all());

test('los prefijos de campos no se repiten entre tablas', function () {
    expect(array_unique(prefijosDeTablas()))->toHaveCount(count(prefijosDeTablas()));
});

test('los índices usan los prefijos uq_ e idx_', function () {
    $indices = collect(array_keys(prefijosDeTablas()))
        ->flatMap(fn (string $tabla) => collect(Schema::getIndexes($tabla))->reject(fn (array $indice): bool => $indice['primary'])->pluck('name'));

    expect($indices->all())->each->toMatch('/^(uq|idx)_[a-z_]+$/')
        ->and(Schema::hasIndex('tb_usuario', 'uq_usuario_correo'))->toBeTrue()
        ->and(Schema::hasIndex('tb_zona_turistica', 'uq_zona_turistica_nombre'))->toBeTrue()
        ->and(Schema::hasIndex('tb_informe_zona', 'uq_informe_zona'))->toBeTrue();
});

test('los valores de dominio referencian sus catálogos con llave foránea', function (string $tabla, string $columna, string $catalogo) {
    $foranea = collect(Schema::getForeignKeys($tabla))->first(fn (array $llave): bool => $llave['columns'] === [$columna]);

    expect($foranea)->not->toBeNull()
        ->and($foranea['foreign_table'])->toBe($catalogo);
})->with([
    'dificultad de la zona' => ['tb_zona_turistica', 'zon_dif_codigo', 'tb_dificultad'],
    'servicio del horario' => ['tb_horario', 'hor_ser_codigo', 'tb_servicio'],
    'fuente de la bitácora' => ['tb_bitacora', 'bit_fue_codigo', 'tb_fuente'],
    'tipo de sincronización' => ['tb_bitacora', 'bit_tsi_codigo', 'tb_tipo_sincronizacion'],
    'resultado de sincronización' => ['tb_bitacora', 'bit_res_codigo', 'tb_resultado_sincronizacion'],
    'zona del informe' => ['tb_informe_zona', 'izo_zon_codigo', 'tb_zona_turistica'],
    'horario del informe' => ['tb_informe_horario', 'iho_hor_codigo', 'tb_horario'],
    'pronóstico del informe' => ['tb_informe_clima', 'icl_cli_codigo', 'tb_clima'],
]);

test('ya no existen el contenido JSON del informe ni los textos reemplazados por catálogos', function () {
    expect(Schema::hasColumn('tb_informe', 'inf_contenido'))->toBeFalse()
        ->and(Schema::hasColumn('tb_zona_turistica', 'zon_dificultad'))->toBeFalse()
        ->and(Schema::hasColumn('tb_horario', 'hor_servicio'))->toBeFalse()
        ->and(Schema::hasColumns('tb_bitacora', ['bit_fuente', 'bit_tipo', 'bit_resultado']))->toBeFalse();
});

test('la base de datos rechaza una referencia a un catálogo inexistente', function () {
    $estacion = Estacion::factory()->create();
    $categoria = Categoria::factory()->create();

    expect(fn () => DB::table('tb_zona_turistica')->insert([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $categoria->getKey(),
        'zon_dif_codigo' => 99,
        'zon_nombre' => 'Zona con dificultad inexistente',
        'zon_descripcion' => 'Prueba de integridad referencial.',
        'zon_latitud' => -13.5,
        'zon_longitud' => -72.1,
        'zon_distancia' => 500,
    ]))->toThrow(QueryException::class);
});

test('las llaves foráneas usan nombres estandarizados en MySQL', function () {
    if (! usaMysql()) {
        $this->markTestSkipped('SQLite no guarda el nombre de las llaves foráneas.');
    }

    $llaves = collect(array_keys(prefijosDeTablas()))
        ->flatMap(fn (string $tabla) => collect(Schema::getForeignKeys($tabla))->pluck('name'));

    expect($llaves)->toHaveCount(25)
        ->and($llaves->all())->each->toMatch('/^fk_[a-z_]+$/')
        ->and($llaves->all())->toContain('fk_horario_estacion_origen', 'fk_horario_estacion_destino', 'fk_informe_zona_informe');
});

test('MySQL rechaza valores fuera de dominio con restricciones CHECK', function () {
    if (! usaMysql()) {
        $this->markTestSkipped('SQLite solo admite restricciones CHECK al crear la tabla.');
    }

    $estacion = Estacion::factory()->create();

    expect(fn () => Clima::factory()->for($estacion)->create(['cli_probabilidad_lluvia' => 150]))->toThrow(QueryException::class)
        ->and(fn () => Clima::factory()->for($estacion)->create(['cli_temperatura_minima' => 20, 'cli_temperatura_maxima' => 10]))->toThrow(QueryException::class)
        ->and(fn () => Horario::factory()->create(['hor_est_codigo_origen' => $estacion->getKey(), 'hor_est_codigo_destino' => $estacion->getKey()]))->toThrow(QueryException::class)
        ->and(fn () => Horario::factory()->create(['hor_precio' => -1]))->toThrow(QueryException::class)
        ->and(fn () => ZonaTuristica::factory()->create(['zon_distancia' => 0]))->toThrow(QueryException::class);
});
