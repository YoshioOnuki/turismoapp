<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_estacion', function (Blueprint $table) {
            $table->index(['est_estado', 'est_nombre'], 'idx_estacion_estado_nombre');
        });

        Schema::table('tb_categoria', function (Blueprint $table) {
            $table->index(['cat_estado', 'cat_nombre'], 'idx_categoria_estado_nombre');
        });

        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->index(
                ['zon_est_codigo', 'zon_estado', 'zon_cat_codigo', 'zon_distancia'],
                'idx_zona_busqueda_turista',
            );
        });

        Schema::table('tb_zona_imagen', function (Blueprint $table) {
            $table->index(['zim_zon_codigo', 'zim_orden'], 'idx_zona_imagen_orden');
        });

        Schema::table('tb_horario', function (Blueprint $table) {
            $table->index(
                ['hor_est_codigo_destino', 'hor_estado', 'hor_hora_llegada'],
                'idx_horario_llegada_estacion',
            );
        });

        Schema::table('tb_informe', function (Blueprint $table) {
            $table->index(
                ['inf_usu_codigo', 'inf_fecha_creacion', 'inf_codigo'],
                'idx_informe_historial_usuario',
            );
        });

        Schema::table('tb_bitacora', function (Blueprint $table) {
            $table->index(
                ['bit_fuente', 'bit_resultado', 'bit_fecha_fin'],
                'idx_bitacora_estado_fuente',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_bitacora', function (Blueprint $table) {
            $table->dropIndex('idx_bitacora_estado_fuente');
        });

        Schema::table('tb_informe', function (Blueprint $table) {
            $table->dropIndex('idx_informe_historial_usuario');
        });

        Schema::table('tb_horario', function (Blueprint $table) {
            $table->dropIndex('idx_horario_llegada_estacion');
        });

        Schema::table('tb_zona_imagen', function (Blueprint $table) {
            $table->dropIndex('idx_zona_imagen_orden');
        });

        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->dropIndex('idx_zona_busqueda_turista');
        });

        Schema::table('tb_categoria', function (Blueprint $table) {
            $table->dropIndex('idx_categoria_estado_nombre');
        });

        Schema::table('tb_estacion', function (Blueprint $table) {
            $table->dropIndex('idx_estacion_estado_nombre');
        });
    }
};
