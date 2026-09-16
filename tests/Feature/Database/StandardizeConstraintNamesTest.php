<?php

use App\Models\Categoria;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @return array<string, list<list<string>>>
 */
function columnasDeIndicesUnicosNormalizados(): array
{
    $indices = [];

    foreach (['tb_perfil', 'tb_usuario', 'tb_categoria', 'tb_preferencia', 'tb_estacion', 'tb_clima', 'tb_horario', 'tb_informe_categoria', 'tb_parametro'] as $tabla) {
        $indices[$tabla] = collect(Schema::getIndexes($tabla))
            ->filter(fn (array $indice): bool => $indice['unique'] && ! $indice['primary'])
            ->pluck('columns')->all();
    }

    return $indices;
}

test('normaliza los índices sin cambiar columnas ni perder registros o unicidad', function () {
    if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
        $this->markTestSkipped('La compatibilidad de esta migración se verifica en MySQL y MariaDB.');
    }

    $migracion = require database_path('migrations/2026_09_14_000006_standardize_constraint_names.php');
    $migracion->down();
    $usuario = Usuario::factory()->create();
    $categoria = Categoria::factory()->create();
    $preferencia = ['pre_usu_codigo' => $usuario->getKey(), 'pre_cat_codigo' => $categoria->getKey()];
    DB::table('tb_preferencia')->insert($preferencia);
    $columnas = columnasDeIndicesUnicosNormalizados();

    $migracion->up();

    expect(columnasDeIndicesUnicosNormalizados())->toBe($columnas);
    $this->assertModelExists($usuario);
    $this->assertModelExists($categoria);
    $this->assertDatabaseHas('tb_preferencia', $preferencia);
    expect(fn () => DB::table('tb_preferencia')->insert($preferencia))->toThrow(QueryException::class);
    expect(fn () => Usuario::factory()->create(['usu_correo' => $usuario->usu_correo]))->toThrow(QueryException::class);

    $usuario->delete();
    $categoria->delete();
});

test('revierte los nombres de índices sin perder registros ni permitir preferencias duplicadas', function () {
    if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
        $this->markTestSkipped('La compatibilidad de esta migración se verifica en MySQL y MariaDB.');
    }

    $migracion = require database_path('migrations/2026_09_14_000006_standardize_constraint_names.php');
    $usuario = Usuario::factory()->create();
    $categoria = Categoria::factory()->create();
    $preferencia = ['pre_usu_codigo' => $usuario->getKey(), 'pre_cat_codigo' => $categoria->getKey()];
    DB::table('tb_preferencia')->insert($preferencia);
    $columnas = columnasDeIndicesUnicosNormalizados();

    $migracion->down();

    expect(columnasDeIndicesUnicosNormalizados())->toBe($columnas);
    expect(Schema::hasIndex('tb_usuario', 'tb_usuario_usu_correo_unique'))->toBeTrue();
    expect(Schema::hasIndex('tb_usuario', 'uq_usuario_correo'))->toBeFalse();
    $this->assertModelExists($usuario);
    $this->assertDatabaseHas('tb_preferencia', $preferencia);
    expect(fn () => DB::table('tb_preferencia')->insert($preferencia))->toThrow(QueryException::class);

    $migracion->up();
    $usuario->delete();
    $categoria->delete();
});
