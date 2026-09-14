<?php

use Illuminate\Support\Facades\Schema;

test('las consultas principales cuentan con índices compuestos', function (string $tabla, string $indice) {
    expect(Schema::hasIndex($tabla, $indice))->toBeTrue();
})->with([
    'estaciones activas' => ['tb_estacion', 'idx_estacion_estado_nombre'],
    'categorías activas' => ['tb_categoria', 'idx_categoria_estado_nombre'],
    'zonas sugeridas' => ['tb_zona_turistica', 'idx_zona_busqueda_turista'],
    'imágenes ordenadas' => ['tb_zona_imagen', 'idx_zona_imagen_orden'],
    'trenes de llegada' => ['tb_horario', 'idx_horario_llegada_estacion'],
    'historial del turista' => ['tb_informe', 'idx_informe_historial_usuario'],
    'vigencia de fuentes' => ['tb_bitacora', 'idx_bitacora_fuente_resultado_fecha'],
]);
