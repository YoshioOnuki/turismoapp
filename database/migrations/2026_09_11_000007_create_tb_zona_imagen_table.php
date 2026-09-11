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
        Schema::create('tb_zona_imagen', function (Blueprint $table) {
            $table->id('zim_codigo');
            $table->foreignId('zim_zon_codigo')->constrained('tb_zona_turistica', 'zon_codigo')->cascadeOnDelete();
            $table->string('zim_ruta');
            $table->unsignedTinyInteger('zim_orden')->default(0);
            $table->timestamp('zim_fecha_creacion')->nullable();
            $table->timestamp('zim_fecha_actualizacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_zona_imagen');
    }
};
