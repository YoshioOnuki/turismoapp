<?php

use App\Enums\TipoPerfil;
use App\Models\Estacion;
use App\Models\Usuario;

test('travel group consulta las estaciones en solo lectura', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create();
    Estacion::factory()->create([
        'est_codigo_externo' => 'EST-01',
        'est_nombre' => 'Estación Central',
    ]);

    $this->actingAs($usuario)
        ->get(route('travel-group.estaciones'))
        ->assertOk()
        ->assertSeeInOrder(['Estaciones ferroviarias', 'Estación Central', 'EST-01'])
        ->assertDontSee('Editar')
        ->assertDontSee('Eliminar');
});

test('un turista no puede consultar el listado administrativo de estaciones', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();

    $this->actingAs($usuario)
        ->get(route('travel-group.estaciones'))
        ->assertForbidden();
});
