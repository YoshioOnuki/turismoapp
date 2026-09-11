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
        Schema::create('tb_clima', function (Blueprint $table) {
            $table->id('cli_codigo');
            $table->foreignId('cli_est_codigo')->constrained('tb_estacion', 'est_codigo')->cascadeOnDelete();
            $table->date('cli_fecha');
            $table->decimal('cli_temperatura_minima', 4, 1);
            $table->decimal('cli_temperatura_maxima', 4, 1);
            $table->unsignedTinyInteger('cli_probabilidad_lluvia')->comment('Porcentaje de 0 a 100');
            $table->string('cli_descripcion', 150);
            $table->timestamp('cli_fecha_creacion')->nullable();
            $table->timestamp('cli_fecha_actualizacion')->nullable();
            $table->unique(['cli_est_codigo', 'cli_fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_clima');
    }
};
