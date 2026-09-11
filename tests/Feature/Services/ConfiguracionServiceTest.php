<?php

use App\Models\Parametro;
use App\Services\ConfiguracionService;

test('actualiza un parámetro general', function () {
    $parametro = Parametro::factory()->create(['par_valor' => '24']);

    app(ConfiguracionService::class)->actualizarParametro($parametro->getKey(), '12');

    expect($parametro->fresh()->par_valor)->toBe('12');
});

test('crea actualiza y da de baja categorías sin eliminarlas', function () {
    $servicio = app(ConfiguracionService::class);

    $categoria = $servicio->guardarCategoria(null, 'Cultura');
    $servicio->guardarCategoria($categoria->getKey(), 'Cultura viva');
    $servicio->cambiarEstadoCategoria($categoria->getKey());

    expect($categoria->fresh()->cat_nombre)->toBe('Cultura viva')
        ->and($categoria->fresh()->cat_estado)->toBeFalse();
    $this->assertDatabaseCount('tb_categoria', 1);
});
