<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Texto que guardaba zon_dificultad y su código en tb_dificultad (App\Enums\Dificultad).
     *
     * @var array<string, int>
     */
    private const CODIGOS = ['baja' => 1, 'media' => 2, 'alta' => 3];

    /**
     * Run the migrations.
     *
     * Reemplaza el texto de la dificultad por un catálogo referenciado con llave foránea.
     * Un valor no reconocido se asigna a la dificultad media para no perder la zona.
     */
    public function up(): void
    {
        Schema::create('tb_dificultad', function (Blueprint $table) {
            $table->id('dif_codigo');
            $table->string('dif_nombre', 20)->unique('uq_dificultad_nombre');
        });

        DB::table('tb_dificultad')->insert([
            ['dif_codigo' => 1, 'dif_nombre' => 'Baja'],
            ['dif_codigo' => 2, 'dif_nombre' => 'Media'],
            ['dif_codigo' => 3, 'dif_nombre' => 'Alta'],
        ]);

        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->unsignedBigInteger('zon_dif_codigo')->nullable()->after('zon_distancia');
        });

        foreach (self::CODIGOS as $valor => $codigo) {
            DB::table('tb_zona_turistica')->where('zon_dificultad', $valor)->update(['zon_dif_codigo' => $codigo]);
        }

        DB::table('tb_zona_turistica')->whereNull('zon_dif_codigo')->update(['zon_dif_codigo' => self::CODIGOS['media']]);

        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->unsignedBigInteger('zon_dif_codigo')->nullable(false)->change();
            $table->index('zon_dif_codigo', 'idx_zona_turistica_dificultad');
            $table->foreign('zon_dif_codigo', 'fk_zona_turistica_dificultad')->references('dif_codigo')->on('tb_dificultad');
        });

        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->dropColumn('zon_dificultad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->string('zon_dificultad', 10)->nullable()->after('zon_distancia');
        });

        foreach (self::CODIGOS as $valor => $codigo) {
            DB::table('tb_zona_turistica')->where('zon_dif_codigo', $codigo)->update(['zon_dificultad' => $valor]);
        }

        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->dropForeign(DB::getDriverName() === 'sqlite' ? ['zon_dif_codigo'] : 'fk_zona_turistica_dificultad');
        });

        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->dropIndex('idx_zona_turistica_dificultad');
            $table->string('zon_dificultad', 10)->nullable(false)->change();
        });

        Schema::table('tb_zona_turistica', function (Blueprint $table) {
            $table->dropColumn('zon_dif_codigo');
        });

        Schema::dropIfExists('tb_dificultad');
    }
};
