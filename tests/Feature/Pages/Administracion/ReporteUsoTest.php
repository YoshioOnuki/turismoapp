<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Usuario;

test('el administrador consulta el reporte de uso', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación Central']);
    $categoria = Categoria::factory()->create(['cat_nombre' => 'Naturaleza']);
    $informe = Informe::factory()->create(['inf_est_codigo' => $estacion->getKey()]);
    $informe->categorias()->attach($categoria);

    $this->actingAs($administrador)
        ->get(route('administracion.reporte-uso'))
        ->assertOk()
        ->assertSeeInOrder(['Reporte de uso', 'Estaciones más consultadas', 'Estación Central', 'Categorías más consultadas', 'Naturaleza']);
});
