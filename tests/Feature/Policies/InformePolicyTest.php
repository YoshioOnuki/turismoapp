<?php

use App\Enums\TipoPerfil;
use App\Models\Informe;
use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;

test('solo el turista propietario puede ver y exportar un informe', function (TipoPerfil $perfil, bool $esPropietario, bool $permitido) {
    $propietario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $usuario = $esPropietario
        ? $propietario
        : Usuario::factory()->conPerfil($perfil)->create();
    $informe = Informe::factory()->create(['inf_usu_codigo' => $propietario->getKey()]);

    expect(Gate::forUser($usuario)->allows('ver', $informe))->toBe($permitido)
        ->and(Gate::forUser($usuario)->allows('exportar', $informe))->toBe($permitido);
})->with([
    'turista propietario' => [TipoPerfil::UsuarioFinal, true, true],
    'otro turista' => [TipoPerfil::UsuarioFinal, false, false],
    'travel group' => [TipoPerfil::TravelGroup, false, false],
    'administrador mtc' => [TipoPerfil::AdministradorMtc, false, false],
]);
