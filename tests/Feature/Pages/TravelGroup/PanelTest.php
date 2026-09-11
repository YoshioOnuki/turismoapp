<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;

test('muestra a Travel Group Perú sus módulos', function () {
    $this->actingAs(Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create())
        ->get(route('travel-group.panel'))
        ->assertSeeInOrder(['Zonas turísticas', 'Estaciones', 'Zonas por estación']);
});
