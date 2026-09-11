<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Parametro;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use App\Services\PlanificacionService;

test('filtra zonas activas por estación preferencias y distancia máxima', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create();
    $preferida = Categoria::factory()->create();
    $otra = Categoria::factory()->create();
    $usuario->preferencias()->attach($preferida);
    Parametro::factory()->create(['par_clave' => 'distancia_maxima_caminable', 'par_valor' => '1000']);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $preferida->getKey(),
        'zon_nombre' => 'Zona disponible',
        'zon_distancia' => 900,
    ]);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $preferida->getKey(),
        'zon_nombre' => 'Zona lejana',
        'zon_distancia' => 1100,
    ]);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $otra->getKey(),
        'zon_nombre' => 'Otra categoría',
        'zon_distancia' => 500,
    ]);

    $zonas = app(PlanificacionService::class)->zonasDisponibles($usuario, $estacion->getKey());

    expect($zonas)->toHaveCount(1)
        ->and($zonas[0]['nombre'])->toBe('Zona disponible');
});
