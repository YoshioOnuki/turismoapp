<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Informe;
use App\Models\Usuario;

test('muestra al turista un resumen y sus módulos', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create(['usu_nombre' => 'Ana Quispe']);
    $turista->preferencias()->attach(Categoria::factory()->count(2)->create());
    Informe::factory()->count(3)->create(['inf_usu_codigo' => $turista->getKey()]);

    $this->actingAs($turista)
        ->get(route('turista.panel'))
        ->assertSee('Hola, Ana Quispe')
        ->assertSeeInOrder(['Intereses', '2', 'Informes', '3'])
        ->assertSeeInOrder(['Mis preferencias', 'Planificar mi visita', 'Mis informes']);
});

test('muestra el mensaje de confirmación después de guardar las preferencias', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();

    $this->actingAs($turista)
        ->withSession(['estado' => 'Tus preferencias se guardaron correctamente. Ya puedes empezar a planificar.'])
        ->get(route('turista.panel'))
        ->assertSee('Tus preferencias se guardaron correctamente. Ya puedes empezar a planificar.');
});
