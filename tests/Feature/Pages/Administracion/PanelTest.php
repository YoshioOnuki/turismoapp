<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;

test('muestra al administrador sus módulos', function () {
    $this->actingAs(Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create())
        ->get(route('administracion.panel'))
        ->assertSeeInOrder(['Usuarios', 'Parámetros y categorías', 'Sincronización', 'Horarios y precios', 'Reporte de uso']);
});
