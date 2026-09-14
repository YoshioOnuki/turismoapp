<?php

use App\Enums\TipoPerfil;
use App\Models\Informe;
use App\Models\Usuario;

test('la antigua url html abre la vista de detalle del informe', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $informe = Informe::factory()->create(['inf_usu_codigo' => $turista->getKey()]);

    $this->actingAs($turista)
        ->get(route('turista.informes.html', $informe))
        ->assertRedirectToRoute('turista.informes.detalle', $informe);
});

test('un turista no puede usar la antigua url html de otro usuario', function () {
    $propietario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $otroTurista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $informe = Informe::factory()->create(['inf_usu_codigo' => $propietario->getKey()]);

    $this->actingAs($otroTurista)
        ->get(route('turista.informes.html', $informe))
        ->assertForbidden();
});
