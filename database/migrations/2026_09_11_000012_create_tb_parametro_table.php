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
        Schema::create('tb_parametro', function (Blueprint $table) {
            $table->id('par_codigo');
            $table->string('par_clave', 60)->unique();
            $table->string('par_valor');
            $table->string('par_descripcion')->nullable();
            $table->timestamp('par_fecha_creacion')->nullable();
            $table->timestamp('par_fecha_actualizacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_parametro');
    }
};
