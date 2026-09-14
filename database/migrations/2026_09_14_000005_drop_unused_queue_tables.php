<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas técnicas de Laravel que se conservan. Sus nombres y campos los define el
     * framework, por eso quedan fuera de la convención de prefijos (RNF-15).
     *
     * @var array<string, string>
     */
    private const TABLAS_TECNICAS = [
        'sessions' => 'Tabla técnica de Laravel: sesiones de los usuarios autenticados',
        'cache' => 'Tabla técnica de Laravel: caché del limitador de intentos de inicio de sesión',
        'cache_locks' => 'Tabla técnica de Laravel: bloqueos de la caché',
        'password_reset_tokens' => 'Tabla técnica de Laravel: tokens de recuperación de contraseña (RF-04)',
        'migrations' => 'Tabla técnica de Laravel: historial de migraciones aplicadas',
    ];

    /**
     * Run the migrations.
     *
     * El sistema no usa colas: los correos y procesos se ejecutan de forma inmediata o con el
     * programador de tareas, por lo que las tablas de trabajos en cola se eliminan.
     */
    public function up(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');

        foreach (self::TABLAS_TECNICAS as $tabla => $comentario) {
            Schema::table($tabla, function (Blueprint $table) use ($comentario) {
                $table->comment($comentario);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }
};
