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
        Schema::create('tb_categoria', function (Blueprint $table) {
            $table->id('cat_codigo');
            $table->string('cat_nombre', 60)->unique();
            $table->boolean('cat_estado')->default(true);
            $table->timestamp('cat_fecha_creacion')->nullable();
            $table->timestamp('cat_fecha_actualizacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_categoria');
    }
};
