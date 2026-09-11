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
        Schema::create('tb_zona_turistica', function (Blueprint $table) {
            $table->id('zon_codigo');
            $table->foreignId('zon_est_codigo')->constrained('tb_estacion', 'est_codigo');
            $table->foreignId('zon_cat_codigo')->constrained('tb_categoria', 'cat_codigo');
            $table->string('zon_nombre', 150);
            $table->text('zon_descripcion');
            $table->decimal('zon_latitud', 10, 7);
            $table->decimal('zon_longitud', 10, 7);
            $table->unsignedInteger('zon_distancia')->comment('Distancia a pie desde la estación, solo ida, en metros');
            $table->string('zon_dificultad', 10);
            $table->boolean('zon_estado')->default(true);
            $table->timestamp('zon_fecha_creacion')->nullable();
            $table->timestamp('zon_fecha_actualizacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_zona_turistica');
    }
};
