<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use App\Services\InformeService;

test('genera una copia del informe y sus categorías para el historial', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación Central']);
    $categoria = Categoria::factory()->create();
    $usuario->preferencias()->attach($categoria);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $categoria->getKey(),
    ]);

    $informe = app(InformeService::class)->generar($usuario, $estacion->getKey());

    expect($informe->inf_contenido['estacion'])->toBe('Estación Central')
        ->and($informe->inf_contenido['zonas'])->toHaveCount(1);
    $this->assertDatabaseHas('tb_informe', [
        'inf_codigo' => $informe->getKey(),
        'inf_usu_codigo' => $usuario->getKey(),
    ]);
    $this->assertDatabaseHas('tb_informe_categoria', [
        'ica_inf_codigo' => $informe->getKey(),
        'ica_cat_codigo' => $categoria->getKey(),
    ]);
});

test('el historial contiene únicamente los informes del usuario', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $otroUsuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    Informe::factory()->create(['inf_usu_codigo' => $usuario->getKey()]);
    Informe::factory()->create(['inf_usu_codigo' => $otroUsuario->getKey()]);

    $historial = app(InformeService::class)->listar($usuario);

    expect($historial)->toHaveCount(1);
});
