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
        'consulta_zonas' => Gate::forUser($usuario)->allows('consultar', ZonaTuristica::class),
        'estaciones' => Gate::forUser($usuario)->allows('consultarAdministracion', Estacion::class),
        'seleccion_estacion' => Gate::forUser($usuario)->allows('seleccionar', Estacion::class),
        'usuarios' => Gate::forUser($usuario)->allows('administrar', Usuario::class),
        'categorias' => Gate::forUser($usuario)->allows('administrar', Categoria::class),
        'preferencias' => Gate::forUser($usuario)->allows('seleccionar', Categoria::class),
        'parametros' => Gate::forUser($usuario)->allows('administrar', Parametro::class),
        'sincronizacion' => Gate::forUser($usuario)->allows('administrar', Bitacora::class),
    ];

    expect($permisos)->toBe($permisosEsperados);
})->with([
    'usuario final' => [TipoPerfil::UsuarioFinal, [
        'zonas' => false,
        'consulta_zonas' => true,
        'estaciones' => false,
        'seleccion_estacion' => true,
        'usuarios' => false,
        'categorias' => false,
        'preferencias' => true,
        'parametros' => false,
        'sincronizacion' => false,
    ]],
    'travel group' => [TipoPerfil::TravelGroup, [
        'zonas' => true,
        'consulta_zonas' => false,
        'estaciones' => true,
        'seleccion_estacion' => false,
        'usuarios' => false,
        'categorias' => false,
        'preferencias' => false,
        'parametros' => false,
        'sincronizacion' => false,
    ]],
    'administrador mtc' => [TipoPerfil::AdministradorMtc, [
        'zonas' => false,
        'consulta_zonas' => false,
        'estaciones' => false,
        'seleccion_estacion' => false,
        'usuarios' => true,
        'categorias' => true,
        'preferencias' => false,
        'parametros' => true,
        'sincronizacion' => true,
    ]],
]);
