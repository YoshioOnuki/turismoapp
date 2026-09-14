<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;

test('presenta el servicio y cómo empezar a los invitados', function () {
    $this->get(route('inicio'))
        ->assertOk()
        ->assertSee('Descubre a pie lo mejor de cada estación de tren')
        ->assertSeeInOrder(['Marca lo que te interesa', 'Elige la estación', 'Guarda y revisa'])
        ->assertSeeInOrder(['PeruRail', 'SENAMHI', 'Travel Group Perú'])
        ->assertSee('Crear mi cuenta');
});

test('ofrece al usuario autenticado el acceso a su panel', function () {
    $this->actingAs(Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create())
        ->get(route('inicio'))
        ->assertOk()
        ->assertSee('Ir a mi panel')
        ->assertSee(route('travel-group.panel'))
        ->assertDontSee('Crear mi cuenta');
});
