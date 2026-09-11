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
        Schema::create('tb_usuario', function (Blueprint $table) {
            $table->id('usu_codigo');
            $table->foreignId('usu_per_codigo')->constrained('tb_perfil', 'per_codigo');
            $table->string('usu_nombre');
            $table->string('usu_correo')->unique();
            $table->string('usu_clave');
            $table->boolean('usu_estado')->default(true);
            $table->string('usu_token_recordar', 100)->nullable();
            $table->timestamp('usu_fecha_creacion')->nullable();
            $table->timestamp('usu_fecha_actualizacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_usuario');
    }
};
