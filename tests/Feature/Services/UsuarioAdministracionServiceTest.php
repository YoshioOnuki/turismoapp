<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;
use App\Services\UsuarioAdministracionService;

test('crea usuarios y administra su perfil y estado', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    $servicio = app(UsuarioAdministracionService::class);
    $usuario = $servicio->crear('Operador', 'operador@example.test', 'Clave1234', TipoPerfil::TravelGroup);

    $servicio->cambiarPerfil($usuario->getKey(), TipoPerfil::AdministradorMtc, $administrador);
    $servicio->cambiarEstado($usuario->getKey(), $administrador);

    expect($usuario->fresh()->tipoPerfil())->toBe(TipoPerfil::AdministradorMtc)
        ->and($usuario->fresh()->usu_estado)->toBeFalse();
});

test('impide que el administrador cambie su propio acceso', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();

    expect(fn () => app(UsuarioAdministracionService::class)->cambiarEstado($administrador->getKey(), $administrador))
        ->toThrow(DomainException::class);
});
