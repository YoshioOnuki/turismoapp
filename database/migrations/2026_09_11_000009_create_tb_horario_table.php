<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cada horario une una estación de origen con una de destino; el informe
     * lista los trenes cuyo destino es la estación seleccionada.
     */
    public function up(): void
    {
        Schema::create('tb_horario', function (Blueprint $table) {
            $table->id('hor_codigo');
            $table->string('hor_codigo_externo', 30)->unique()->comment('Código del tren en PeruRail; evita duplicados al sincronizar');
            $table->foreignId('hor_est_codigo_origen')->constrained('tb_estacion', 'est_codigo');
            $table->foreignId('hor_est_codigo_destino')->constrained('tb_estacion', 'est_codigo');
            $table->string('hor_servicio', 60);
            $table->time('hor_hora_salida');
            $table->time('hor_hora_llegada');
            $table->decimal('hor_precio', 8, 2)->comment('Precio del boleto en soles');
            $table->boolean('hor_estado')->default(true);
            $table->timestamp('hor_fecha_creacion')->nullable();
            $table->timestamp('hor_fecha_actualizacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_horario');
    }
};
