<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;

test('muestra al turista sus módulos', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create(['usu_nombre' => 'Ana Quispe']);

    $this->actingAs($turista)
        ->get(route('turista.panel'))
        ->assertSee('Hola, Ana Quispe')
        ->assertSeeInOrder(['Mis preferencias', 'Planificar mi visita', 'Mis informes']);
});
