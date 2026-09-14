<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogos de la bitácora: tabla, prefijo, columna anterior, columna nueva y
     * código de cada valor. Los códigos coinciden con los enums de App\Enums.
     *
     * @var array<string, array{prefijo: string, anterior: string, nueva: string, llave: string, indice: string, valores: array<string, array{codigo: int, nombre: string}>}>
     */
    private const CATALOGOS = [
        'tb_fuente' => [
            'prefijo' => 'fue',
            'anterior' => 'bit_fuente',
            'nueva' => 'bit_fue_codigo',
            'llave' => 'fk_bitacora_fuente',
            'indice' => 'idx_bitacora_fuente_resultado_fecha',
            'valores' => [
                'perurail' => ['codigo' => 1, 'nombre' => 'PeruRail'],
                'senamhi' => ['codigo' => 2, 'nombre' => 'SENAMHI'],
                'travel_group' => ['codigo' => 3, 'nombre' => 'Travel Group Perú'],
            ],
        ],
        'tb_tipo_sincronizacion' => [
            'prefijo' => 'tsi',
            'anterior' => 'bit_tipo',
            'nueva' => 'bit_tsi_codigo',
            'llave' => 'fk_bitacora_tipo_sincronizacion',
            'indice' => 'idx_bitacora_tipo_sincronizacion',
            'valores' => [
                'automatica' => ['codigo' => 1, 'nombre' => 'Automática'],
                'manual' => ['codigo' => 2, 'nombre' => 'Manual'],
            ],
        ],
        'tb_resultado_sincronizacion' => [
            'prefijo' => 'res',
            'anterior' => 'bit_resultado',
            'nueva' => 'bit_res_codigo',
            'llave' => 'fk_bitacora_resultado_sincronizacion',
            'indice' => 'idx_bitacora_resultado_sincronizacion',
            'valores' => [
                'exito' => ['codigo' => 1, 'nombre' => 'Éxito'],
                'error' => ['codigo' => 2, 'nombre' => 'Error'],
            ],
        ],
    ];

    /**
     * Run the migrations.
     *
     * La fuente, el tipo y el resultado de cada sincronización dejan de guardarse como
     * texto y pasan a catálogos referenciados con llave foránea.
     */
    public function up(): void
    {
        foreach (self::CATALOGOS as $tabla => $catalogo) {
            $prefijo = $catalogo['prefijo'];
            $nombreTabla = substr($tabla, 3);

            Schema::create($tabla, function (Blueprint $table) use ($prefijo, $nombreTabla) {
                $table->id("{$prefijo}_codigo");
                $table->string("{$prefijo}_nombre", 30)->unique("uq_{$nombreTabla}_nombre");
            });

            DB::table($tabla)->insert(array_map(
                fn (array $valor): array => ["{$prefijo}_codigo" => $valor['codigo'], "{$prefijo}_nombre" => $valor['nombre']],
                array_values($catalogo['valores']),
            ));
        }

        Schema::table('tb_bitacora', function (Blueprint $table) {
            $table->dropIndex('idx_bitacora_estado_fuente');
        });

        Schema::table('tb_bitacora', function (Blueprint $table) {
            $table->unsignedBigInteger('bit_fue_codigo')->nullable()->after('bit_usu_codigo');
            $table->unsignedBigInteger('bit_tsi_codigo')->nullable()->after('bit_fue_codigo');
            $table->unsignedBigInteger('bit_res_codigo')->nullable()->after('bit_registros');
        });

        foreach (self::CATALOGOS as $catalogo) {
            foreach ($catalogo['valores'] as $texto => $valor) {
                DB::table('tb_bitacora')->where($catalogo['anterior'], $texto)->update([$catalogo['nueva'] => $valor['codigo']]);
            }
        }

        Schema::table('tb_bitacora', function (Blueprint $table) {
            foreach (self::CATALOGOS as $tabla => $catalogo) {
                $table->unsignedBigInteger($catalogo['nueva'])->nullable(false)->change();
                $table->foreign($catalogo['nueva'], $catalogo['llave'])->references("{$catalogo['prefijo']}_codigo")->on($tabla);
            }

            $table->index(['bit_fue_codigo', 'bit_res_codigo', 'bit_fecha_fin'], 'idx_bitacora_fuente_resultado_fecha');
            $table->index('bit_tsi_codigo', 'idx_bitacora_tipo_sincronizacion');
            $table->index('bit_res_codigo', 'idx_bitacora_resultado_sincronizacion');
        });

        Schema::table('tb_bitacora', function (Blueprint $table) {
            $table->dropColumn(['bit_fuente', 'bit_tipo', 'bit_resultado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_bitacora', function (Blueprint $table) {
            $table->string('bit_fuente', 20)->nullable()->after('bit_usu_codigo');
            $table->string('bit_tipo', 15)->nullable()->after('bit_fuente');
            $table->string('bit_resultado', 10)->nullable()->after('bit_registros');
        });

        foreach (self::CATALOGOS as $catalogo) {
            foreach ($catalogo['valores'] as $texto => $valor) {
                DB::table('tb_bitacora')->where($catalogo['nueva'], $valor['codigo'])->update([$catalogo['anterior'] => $texto]);
            }
        }

        Schema::table('tb_bitacora', function (Blueprint $table) {
            foreach (self::CATALOGOS as $catalogo) {
                $table->dropForeign(DB::getDriverName() === 'sqlite' ? [$catalogo['nueva']] : $catalogo['llave']);
            }
        });

        Schema::table('tb_bitacora', function (Blueprint $table) {
            foreach (self::CATALOGOS as $catalogo) {
                $table->dropIndex($catalogo['indice']);
            }

            $table->string('bit_fuente', 20)->nullable(false)->change();
            $table->string('bit_tipo', 15)->nullable(false)->change();
            $table->string('bit_resultado', 10)->nullable(false)->change();
        });

        Schema::table('tb_bitacora', function (Blueprint $table) {
            $table->dropColumn(['bit_fue_codigo', 'bit_tsi_codigo', 'bit_res_codigo']);
            $table->index(['bit_fuente', 'bit_resultado', 'bit_fecha_fin'], 'idx_bitacora_estado_fuente');
        });

        foreach (array_keys(self::CATALOGOS) as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
