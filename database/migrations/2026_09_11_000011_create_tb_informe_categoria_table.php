<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Categorías usadas en cada informe, para el reporte de las más consultadas (RF-24).
     */
    public function up(): void
    {
        Schema::create('tb_informe_categoria', function (Blueprint $table) {
            $table->id('ica_codigo');
            $table->foreignId('ica_inf_codigo')->constrained('tb_informe', 'inf_codigo')->cascadeOnDelete();
            $table->foreignId('ica_cat_codigo')->constrained('tb_categoria', 'cat_codigo')->cascadeOnDelete();
            $table->unique(['ica_inf_codigo', 'ica_cat_codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_informe_categoria');
    }
};
