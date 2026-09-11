<?php

use App\Enums\TipoPerfil;
use App\Models\Estacion;
use App\Models\Usuario;
use App\Models\ZonaTuristica;

test('travel group consulta el reporte de zonas por estación', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create();
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación Central']);
    ZonaTuristica::factory()->create(['zon_est_codigo' => $estacion->getKey(), 'zon_nombre' => 'Mirador Central']);

    $this->actingAs($usuario)
        ->get(route('travel-group.reporte-zonas'))
        ->assertOk()
        ->assertSeeInOrder(['Zonas por estación', 'Estación Central', 'Mirador Central']);
});
