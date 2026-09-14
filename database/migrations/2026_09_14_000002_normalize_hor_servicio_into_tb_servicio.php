<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Los nombres de servicio (Vistadome, Expedition...) se repetían en cada horario;
     * pasan a un catálogo propio referenciado por hor_ser_codigo.
     */
    public function up(): void
    {
        Schema::create('tb_servicio', function (Blueprint $table) {
            $table->id('ser_codigo');
            $table->string('ser_nombre', 60)->unique('uq_servicio_nombre');
            $table->timestamp('ser_fecha_creacion')->nullable();
            $table->timestamp('ser_fecha_actualizacion')->nullable();
        });

        Schema::table('tb_horario', function (Blueprint $table) {
            $table->unsignedBigInteger('hor_ser_codigo')->nullable()->after('hor_est_codigo_destino');
        });

        $ahora = now();

        foreach (DB::table('tb_horario')->distinct()->orderBy('hor_servicio')->pluck('hor_servicio') as $nombre) {
            $codigo = DB::table('tb_servicio')->where('ser_nombre', $nombre)->value('ser_codigo')
                ?? DB::table('tb_servicio')->insertGetId([
                    'ser_nombre' => $nombre,
                    'ser_fecha_creacion' => $ahora,
                    'ser_fecha_actualizacion' => $ahora,
                ], 'ser_codigo');

            DB::table('tb_horario')->where('hor_servicio', $nombre)->update(['hor_ser_codigo' => $codigo]);
        }

        Schema::table('tb_horario', function (Blueprint $table) {
            $table->unsignedBigInteger('hor_ser_codigo')->nullable(false)->change();
            $table->index('hor_ser_codigo', 'idx_horario_servicio');
            $table->foreign('hor_ser_codigo', 'fk_horario_servicio')->references('ser_codigo')->on('tb_servicio');
        });

        Schema::table('tb_horario', function (Blueprint $table) {
            $table->dropColumn('hor_servicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_horario', function (Blueprint $table) {
            $table->string('hor_servicio', 60)->nullable()->after('hor_est_codigo_destino');
        });

        foreach (DB::table('tb_servicio')->pluck('ser_nombre', 'ser_codigo') as $codigo => $nombre) {
            DB::table('tb_horario')->where('hor_ser_codigo', $codigo)->update(['hor_servicio' => $nombre]);
        }

        Schema::table('tb_horario', function (Blueprint $table) {
            $table->dropForeign(DB::getDriverName() === 'sqlite' ? ['hor_ser_codigo'] : 'fk_horario_servicio');
        });

        Schema::table('tb_horario', function (Blueprint $table) {
            $table->dropIndex('idx_horario_servicio');
            $table->string('hor_servicio', 60)->nullable(false)->change();
        });

        Schema::table('tb_horario', function (Blueprint $table) {
            $table->dropColumn('hor_ser_codigo');
        });

        Schema::dropIfExists('tb_servicio');
    }
};
