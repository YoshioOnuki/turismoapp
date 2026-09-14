<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * El informe guardaba zonas, trenes y clima en un JSON (no cumplía la 1FN). Ahora cada
     * informe referencia sus zonas, horarios y pronósticos en tablas de detalle y solo guarda
     * los valores calculados o cotizados al generarlo: recorrido, tiempo y precio del boleto.
     */
    public function up(): void
    {
        Schema::create('tb_informe_zona', function (Blueprint $table) {
            $table->id('izo_codigo');
            $table->unsignedBigInteger('izo_inf_codigo');
            $table->unsignedBigInteger('izo_zon_codigo');
            $table->unsignedInteger('izo_distancia_total')->comment('Recorrido de ida y vuelta calculado al generar el informe, en metros');
            $table->unsignedSmallInteger('izo_tiempo_minutos')->comment('Tiempo estimado de caminata calculado al generar el informe');
            $table->unique(['izo_inf_codigo', 'izo_zon_codigo'], 'uq_informe_zona');
            $table->index('izo_zon_codigo', 'idx_informe_zona_zona_turistica');
            $table->foreign('izo_inf_codigo', 'fk_informe_zona_informe')->references('inf_codigo')->on('tb_informe')->cascadeOnDelete();
            $table->foreign('izo_zon_codigo', 'fk_informe_zona_zona_turistica')->references('zon_codigo')->on('tb_zona_turistica');
        });

        Schema::create('tb_informe_horario', function (Blueprint $table) {
            $table->id('iho_codigo');
            $table->unsignedBigInteger('iho_inf_codigo');
            $table->unsignedBigInteger('iho_hor_codigo');
            $table->decimal('iho_precio', 8, 2)->comment('Precio del boleto vigente al generar el informe, en soles');
            $table->unique(['iho_inf_codigo', 'iho_hor_codigo'], 'uq_informe_horario');
            $table->index('iho_hor_codigo', 'idx_informe_horario_horario');
            $table->foreign('iho_inf_codigo', 'fk_informe_horario_informe')->references('inf_codigo')->on('tb_informe')->cascadeOnDelete();
            $table->foreign('iho_hor_codigo', 'fk_informe_horario_horario')->references('hor_codigo')->on('tb_horario');
        });

        Schema::create('tb_informe_clima', function (Blueprint $table) {
            $table->id('icl_codigo');
            $table->unsignedBigInteger('icl_inf_codigo');
            $table->unsignedBigInteger('icl_cli_codigo');
            $table->unique(['icl_inf_codigo', 'icl_cli_codigo'], 'uq_informe_clima');
            $table->index('icl_cli_codigo', 'idx_informe_clima_clima');
            $table->foreign('icl_inf_codigo', 'fk_informe_clima_informe')->references('inf_codigo')->on('tb_informe')->cascadeOnDelete();
            $table->foreign('icl_cli_codigo', 'fk_informe_clima_clima')->references('cli_codigo')->on('tb_clima');
        });

        $this->trasladarContenido();

        Schema::table('tb_informe', function (Blueprint $table) {
            $table->dropColumn('inf_contenido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_informe', function (Blueprint $table) {
            $table->json('inf_contenido')->nullable()->after('inf_est_codigo');
        });

        foreach (DB::table('tb_informe')->pluck('inf_codigo') as $informe) {
            DB::table('tb_informe')->where('inf_codigo', $informe)->update([
                'inf_contenido' => json_encode([
                    'zonas' => DB::table('tb_informe_zona')
                        ->join('tb_zona_turistica', 'zon_codigo', '=', 'izo_zon_codigo')
                        ->where('izo_inf_codigo', $informe)
                        ->get(['zon_codigo as codigo', 'zon_nombre as nombre', 'izo_distancia_total as distancia_total', 'izo_tiempo_minutos as tiempo_minutos'])
                        ->all(),
                    'trenes' => DB::table('tb_informe_horario')
                        ->join('tb_horario', 'hor_codigo', '=', 'iho_hor_codigo')
                        ->where('iho_inf_codigo', $informe)
                        ->get(['hor_codigo as codigo', 'hor_hora_salida as salida', 'hor_hora_llegada as llegada', 'iho_precio as precio'])
                        ->all(),
                    'clima' => DB::table('tb_informe_clima')
                        ->join('tb_clima', 'cli_codigo', '=', 'icl_cli_codigo')
                        ->where('icl_inf_codigo', $informe)
                        ->get(['cli_codigo as codigo', 'cli_descripcion as descripcion', 'cli_probabilidad_lluvia as lluvia'])
                        ->all(),
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }

        Schema::dropIfExists('tb_informe_clima');
        Schema::dropIfExists('tb_informe_horario');
        Schema::dropIfExists('tb_informe_zona');
    }

    /**
     * Copia a las tablas de detalle los registros que el JSON de cada informe referenciaba
     * y que todavía existen en la base de datos.
     */
    private function trasladarContenido(): void
    {
        $zonas = DB::table('tb_zona_turistica')->pluck('zon_distancia', 'zon_codigo');
        $horarios = DB::table('tb_horario')->pluck('hor_precio', 'hor_codigo');
        $climas = DB::table('tb_clima')->pluck('cli_codigo', 'cli_codigo');

        DB::table('tb_informe')
            ->select(['inf_codigo', 'inf_contenido'])
            ->chunkById(200, function (Collection $informes) use ($zonas, $horarios, $climas): void {
                foreach ($informes as $informe) {
                    $contenido = json_decode((string) $informe->inf_contenido, true);

                    if (! is_array($contenido)) {
                        continue;
                    }

                    DB::table('tb_informe_zona')->insertOrIgnore(
                        $this->filasReferenciadas($contenido['zonas'] ?? null, $zonas)
                            ->map(fn (array $zona): array => [
                                'izo_inf_codigo' => $informe->inf_codigo,
                                'izo_zon_codigo' => (int) $zona['codigo'],
                                'izo_distancia_total' => is_numeric($zona['distancia_total'] ?? null)
                                    ? (int) $zona['distancia_total']
                                    : (int) $zonas[$zona['codigo']] * 2,
                                'izo_tiempo_minutos' => is_numeric($zona['tiempo_minutos'] ?? null) ? (int) $zona['tiempo_minutos'] : 0,
                            ])->all(),
                    );

                    DB::table('tb_informe_horario')->insertOrIgnore(
                        $this->filasReferenciadas($contenido['trenes'] ?? null, $horarios)
                            ->map(fn (array $tren): array => [
                                'iho_inf_codigo' => $informe->inf_codigo,
                                'iho_hor_codigo' => (int) $tren['codigo'],
                                'iho_precio' => is_numeric($tren['precio'] ?? null) ? (float) $tren['precio'] : (float) $horarios[$tren['codigo']],
                            ])->all(),
                    );

                    DB::table('tb_informe_clima')->insertOrIgnore(
                        $this->filasReferenciadas($contenido['clima'] ?? null, $climas)
                            ->map(fn (array $clima): array => [
                                'icl_inf_codigo' => $informe->inf_codigo,
                                'icl_cli_codigo' => (int) $clima['codigo'],
                            ])->all(),
                    );
                }
            }, 'inf_codigo');
    }

    /**
     * @param  Collection<int|string, mixed>  $existentes
     * @return Collection<int, array<string, mixed>>
     */
    private function filasReferenciadas(mixed $filas, Collection $existentes): Collection
    {
        return collect(is_array($filas) ? $filas : [])
            ->filter(fn (mixed $fila): bool => is_array($fila) && is_numeric($fila['codigo'] ?? null) && $existentes->has((int) $fila['codigo']))
            ->unique('codigo')
            ->values();
    }
};
