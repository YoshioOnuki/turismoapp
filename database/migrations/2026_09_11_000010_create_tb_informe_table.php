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
        Schema::create('tb_informe', function (Blueprint $table) {
            $table->id('inf_codigo');
            $table->foreignId('inf_usu_codigo')->constrained('tb_usuario', 'usu_codigo')->cascadeOnDelete();
            $table->foreignId('inf_est_codigo')->constrained('tb_estacion', 'est_codigo');
            $table->json('inf_contenido')->comment('Copia del informe consolidado al momento de generarlo');
            $table->timestamp('inf_fecha_creacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_informe');
    }
};
