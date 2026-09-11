<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Relación N:M entre el usuario y las categorías que prefiere (RF-05).
     */
    public function up(): void
    {
        Schema::create('tb_preferencia', function (Blueprint $table) {
            $table->id('pre_codigo');
            $table->foreignId('pre_usu_codigo')->constrained('tb_usuario', 'usu_codigo')->cascadeOnDelete();
            $table->foreignId('pre_cat_codigo')->constrained('tb_categoria', 'cat_codigo')->cascadeOnDelete();
            $table->unique(['pre_usu_codigo', 'pre_cat_codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_preferencia');
    }
};
