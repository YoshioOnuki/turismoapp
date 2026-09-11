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
        Schema::create('tb_bitacora', function (Blueprint $table) {
            $table->id('bit_codigo');
            $table->foreignId('bit_usu_codigo')->nullable()->constrained('tb_usuario', 'usu_codigo')->nullOnDelete();
            $table->string('bit_fuente', 20);
            $table->string('bit_tipo', 15);
            $table->dateTime('bit_fecha_inicio');
            $table->dateTime('bit_fecha_fin');
            $table->unsignedInteger('bit_registros')->default(0);
            $table->string('bit_resultado', 10);
            $table->text('bit_mensaje')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_bitacora');
    }
};
