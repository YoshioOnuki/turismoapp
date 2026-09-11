<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Usuario;
use App\Services\PreferenciaService;

test('guarda y reemplaza únicamente las preferencias del usuario indicado', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $otroUsuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $categorias = Categoria::factory()->count(3)->create();
    $otroUsuario->preferencias()->attach($categorias[2]);
    $servicio = app(PreferenciaService::class);

    $servicio->guardar($usuario, [$categorias[0]->getKey(), $categorias[1]->getKey()]);
    $servicio->guardar($usuario, [$categorias[1]->getKey()]);

    expect($usuario->preferencias()->pluck('tb_categoria.cat_codigo')->all())
        ->toBe([$categorias[1]->getKey()]);
    expect($otroUsuario->preferencias()->pluck('tb_categoria.cat_codigo')->all())
        ->toBe([$categorias[2]->getKey()]);
});
