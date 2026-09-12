<?php

use App\Enums\TipoPerfil;
use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Usuario;

test('el turista consulta su historial de informes en una tabla', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación Central']);
    $informe = Informe::factory()->create([
        'inf_usu_codigo' => $turista->getKey(),
        'inf_est_codigo' => $estacion->getKey(),
    ]);

    $this->actingAs($turista)
        ->get(route('turista.informes'))
        ->assertOk()
        ->assertSeeInOrder(['Mis informes', 'Estación Central'])
        ->assertSee(route('turista.informes.pdf', $informe))
        ->assertSee(route('turista.informes.html', $informe));
});

test('un administrador no puede consultar informes de turistas', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();

    $this->actingAs($administrador)
        ->get(route('turista.informes'))
        ->assertForbidden();
});
