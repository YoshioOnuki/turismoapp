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
        Schema::create('tb_estacion', function (Blueprint $table) {
            $table->id('est_codigo');
            $table->string('est_codigo_externo', 30)->unique()->comment('Código de la estación en PeruRail; evita duplicados al sincronizar');
            $table->string('est_nombre', 100);
            $table->decimal('est_latitud', 10, 7);
            $table->decimal('est_longitud', 10, 7);
            $table->boolean('est_estado')->default(true);
            $table->timestamp('est_fecha_creacion')->nullable();
            $table->timestamp('est_fecha_actualizacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_estacion');
    }
};
