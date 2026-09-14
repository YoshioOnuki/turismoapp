<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índices únicos creados con el nombre automático de Laravel y su nombre estándar.
     *
     * @var array<string, array<string, string>>
     */
    private const UNICOS = [
        'tb_perfil' => ['tb_perfil_per_nombre_unique' => 'uq_perfil_nombre'],
        'tb_usuario' => ['tb_usuario_usu_correo_unique' => 'uq_usuario_correo'],
        'tb_categoria' => ['tb_categoria_cat_nombre_unique' => 'uq_categoria_nombre'],
        'tb_preferencia' => ['tb_preferencia_pre_usu_codigo_pre_cat_codigo_unique' => 'uq_preferencia'],
        'tb_estacion' => ['tb_estacion_est_codigo_externo_unique' => 'uq_estacion_codigo_externo'],
        'tb_clima' => ['tb_clima_cli_est_codigo_cli_fecha_unique' => 'uq_clima_estacion_fecha'],
        'tb_horario' => ['tb_horario_hor_codigo_externo_unique' => 'uq_horario_codigo_externo'],
        'tb_informe_categoria' => ['tb_informe_categoria_ica_inf_codigo_ica_cat_codigo_unique' => 'uq_informe_categoria'],
        'tb_parametro' => ['tb_parametro_par_clave_unique' => 'uq_parametro_clave'],
    ];

    /**
     * Llaves foráneas con nombre automático. "indice" es el índice de apoyo que se crea cuando
     * ningún otro índice empieza por la columna; "al_borrar" conserva la acción original.
     *
     * @var list<array{tabla: string, columna: string, referida: string, referencia: string, nombre: string, indice: ?string, al_borrar: ?string}>
     */
    private const FORANEAS = [
        ['tabla' => 'tb_usuario', 'columna' => 'usu_per_codigo', 'referida' => 'tb_perfil', 'referencia' => 'per_codigo', 'nombre' => 'fk_usuario_perfil', 'indice' => 'idx_usuario_perfil', 'al_borrar' => null],
        ['tabla' => 'tb_preferencia', 'columna' => 'pre_usu_codigo', 'referida' => 'tb_usuario', 'referencia' => 'usu_codigo', 'nombre' => 'fk_preferencia_usuario', 'indice' => null, 'al_borrar' => 'cascade'],
        ['tabla' => 'tb_preferencia', 'columna' => 'pre_cat_codigo', 'referida' => 'tb_categoria', 'referencia' => 'cat_codigo', 'nombre' => 'fk_preferencia_categoria', 'indice' => 'idx_preferencia_categoria', 'al_borrar' => 'cascade'],
        ['tabla' => 'tb_zona_turistica', 'columna' => 'zon_est_codigo', 'referida' => 'tb_estacion', 'referencia' => 'est_codigo', 'nombre' => 'fk_zona_turistica_estacion', 'indice' => null, 'al_borrar' => null],
        ['tabla' => 'tb_zona_turistica', 'columna' => 'zon_cat_codigo', 'referida' => 'tb_categoria', 'referencia' => 'cat_codigo', 'nombre' => 'fk_zona_turistica_categoria', 'indice' => 'idx_zona_turistica_categoria', 'al_borrar' => null],
        ['tabla' => 'tb_zona_imagen', 'columna' => 'zim_zon_codigo', 'referida' => 'tb_zona_turistica', 'referencia' => 'zon_codigo', 'nombre' => 'fk_zona_imagen_zona_turistica', 'indice' => null, 'al_borrar' => 'cascade'],
        ['tabla' => 'tb_clima', 'columna' => 'cli_est_codigo', 'referida' => 'tb_estacion', 'referencia' => 'est_codigo', 'nombre' => 'fk_clima_estacion', 'indice' => null, 'al_borrar' => 'cascade'],
        ['tabla' => 'tb_horario', 'columna' => 'hor_est_codigo_origen', 'referida' => 'tb_estacion', 'referencia' => 'est_codigo', 'nombre' => 'fk_horario_estacion_origen', 'indice' => 'idx_horario_estacion_origen', 'al_borrar' => null],
        ['tabla' => 'tb_horario', 'columna' => 'hor_est_codigo_destino', 'referida' => 'tb_estacion', 'referencia' => 'est_codigo', 'nombre' => 'fk_horario_estacion_destino', 'indice' => null, 'al_borrar' => null],
        ['tabla' => 'tb_informe', 'columna' => 'inf_usu_codigo', 'referida' => 'tb_usuario', 'referencia' => 'usu_codigo', 'nombre' => 'fk_informe_usuario', 'indice' => null, 'al_borrar' => 'cascade'],
        ['tabla' => 'tb_informe', 'columna' => 'inf_est_codigo', 'referida' => 'tb_estacion', 'referencia' => 'est_codigo', 'nombre' => 'fk_informe_estacion', 'indice' => 'idx_informe_estacion', 'al_borrar' => null],
        ['tabla' => 'tb_informe_categoria', 'columna' => 'ica_inf_codigo', 'referida' => 'tb_informe', 'referencia' => 'inf_codigo', 'nombre' => 'fk_informe_categoria_informe', 'indice' => null, 'al_borrar' => 'cascade'],
        ['tabla' => 'tb_informe_categoria', 'columna' => 'ica_cat_codigo', 'referida' => 'tb_categoria', 'referencia' => 'cat_codigo', 'nombre' => 'fk_informe_categoria_categoria', 'indice' => 'idx_informe_categoria_categoria', 'al_borrar' => 'cascade'],
        ['tabla' => 'tb_bitacora', 'columna' => 'bit_usu_codigo', 'referida' => 'tb_usuario', 'referencia' => 'usu_codigo', 'nombre' => 'fk_bitacora_usuario', 'indice' => 'idx_bitacora_usuario', 'al_borrar' => 'null'],
    ];

    /**
     * Run the migrations.
     *
     * Convención: uq_<tabla>_<campos> (uq_<tabla> en tablas intermedias), fk_<tabla>_<tabla
     * referida>[_rol] e idx_<tabla>_<propósito>, sin el prefijo tb_. También agrega el índice
     * único del nombre de la zona turística, que antes solo validaba la aplicación.
     */
    public function up(): void
    {
        foreach (self::UNICOS as $tabla => $indices) {
            Schema::table($tabla, function (Blueprint $table) use ($indices) {
                foreach ($indices as $anterior => $nuevo) {
                    $table->renameIndex($anterior, $nuevo);
                }
            });
        }

        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->unique('zon_nombre', 'uq_zona_turistica_nombre');
        });

        foreach (collect(self::FORANEAS)->groupBy('tabla') as $tabla => $llaves) {
            Schema::table($tabla, function (Blueprint $table) use ($llaves) {
                foreach ($llaves as $llave) {
                    $table->dropForeign([$llave['columna']]);
                }
            });

            Schema::table($tabla, function (Blueprint $table) use ($tabla, $llaves) {
                foreach ($llaves as $llave) {
                    $indiceAutomatico = "{$tabla}_{$llave['columna']}_foreign";

                    if (Schema::hasIndex($tabla, $indiceAutomatico)) {
                        $table->dropIndex($indiceAutomatico);
                    }

                    if ($llave['indice'] !== null) {
                        $table->index($llave['columna'], $llave['indice']);
                    }

                    $foranea = $table->foreign($llave['columna'], $llave['nombre'])
                        ->references($llave['referencia'])
                        ->on($llave['referida']);

                    match ($llave['al_borrar']) {
                        'cascade' => $foranea->cascadeOnDelete(),
                        'null' => $foranea->nullOnDelete(),
                        default => null,
                    };
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (collect(self::FORANEAS)->groupBy('tabla') as $tabla => $llaves) {
            Schema::table($tabla, function (Blueprint $table) use ($llaves) {
                foreach ($llaves as $llave) {
                    $table->dropForeign(DB::getDriverName() === 'sqlite' ? [$llave['columna']] : $llave['nombre']);
                }
            });

            Schema::table($tabla, function (Blueprint $table) use ($llaves) {
                foreach ($llaves as $llave) {
                    if ($llave['indice'] !== null) {
                        $table->dropIndex($llave['indice']);
                    }

                    $foranea = $table->foreign($llave['columna'])
                        ->references($llave['referencia'])
                        ->on($llave['referida']);

                    match ($llave['al_borrar']) {
                        'cascade' => $foranea->cascadeOnDelete(),
                        'null' => $foranea->nullOnDelete(),
                        default => null,
                    };
                }
            });
        }

        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->dropUnique('uq_zona_turistica_nombre');
        });

        foreach (self::UNICOS as $tabla => $indices) {
            Schema::table($tabla, function (Blueprint $table) use ($indices) {
                foreach ($indices as $anterior => $nuevo) {
                    $table->renameIndex($nuevo, $anterior);
                }
            });
        }
    }
};
