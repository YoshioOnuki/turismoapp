<?php

use App\Enums\TipoPerfil;
use App\Models\Bitacora;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Parametro;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use Illuminate\Support\Facades\Gate;

test('las policies aplican la matriz completa de acceso por perfil', function (TipoPerfil $perfil, array $permisosEsperados) {
    $usuario = Usuario::factory()->conPerfil($perfil)->create();

    $permisos = [
        'zonas' => Gate::forUser($usuario)->allows('administrar', ZonaTuristica::class),
        'estaciones' => Gate::forUser($usuario)->allows('consultarAdministracion', Estacion::class),
        'usuarios' => Gate::forUser($usuario)->allows('administrar', Usuario::class),
        'categorias' => Gate::forUser($usuario)->allows('administrar', Categoria::class),
        'parametros' => Gate::forUser($usuario)->allows('administrar', Parametro::class),
        'sincronizacion' => Gate::forUser($usuario)->allows('administrar', Bitacora::class),
    ];

    expect($permisos)->toBe($permisosEsperados);
})->with([
    'usuario final' => [TipoPerfil::UsuarioFinal, [
        'zonas' => false,
        'estaciones' => false,
        'usuarios' => false,
        'categorias' => false,
        'parametros' => false,
        'sincronizacion' => false,
    ]],
    'travel group' => [TipoPerfil::TravelGroup, [
        'zonas' => true,
        'estaciones' => true,
        'usuarios' => false,
        'categorias' => false,
        'parametros' => false,
        'sincronizacion' => false,
    ]],
    'administrador mtc' => [TipoPerfil::AdministradorMtc, [
        'zonas' => false,
        'estaciones' => false,
        'usuarios' => true,
        'categorias' => true,
        'parametros' => true,
        'sincronizacion' => true,
    ]],
]);
