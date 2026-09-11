<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea el catálogo fijo de perfiles (RF-03). Los códigos coinciden con App\Enums\TipoPerfil.
     */
    public function up(): void
    {
        Schema::create('tb_perfil', function (Blueprint $table) {
            $table->id('per_codigo');
            $table->string('per_nombre', 50)->unique();
        });

        DB::table('tb_perfil')->insert([
            ['per_codigo' => 1, 'per_nombre' => 'Usuario final'],
            ['per_codigo' => 2, 'per_nombre' => 'Travel Group Perú'],
            ['per_codigo' => 3, 'per_nombre' => 'Administrador MTC'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_perfil');
    }
};
