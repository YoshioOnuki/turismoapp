<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Clima;
use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Informe;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use App\Services\InformeExportacionService;
use App\Services\InformeService;

test('guarda el informe con sus zonas, trenes, pronósticos y categorías en las tablas de detalle', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación Central']);
    $categoria = Categoria::factory()->create();
    $usuario->preferencias()->attach($categoria);
    $zona = ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $categoria->getKey(),
        'zon_distancia' => 600,
    ]);
    $horario = Horario::factory()->create([
        'hor_est_codigo_destino' => $estacion->getKey(),
        'hor_precio' => 250,
    ]);
    $clima = Clima::factory()->for($estacion)->create();

    $informe = app(InformeService::class)->generar($usuario, $estacion->getKey());

    $this->assertDatabaseHas('tb_informe', [
        'inf_codigo' => $informe->getKey(),
        'inf_usu_codigo' => $usuario->getKey(),
        'inf_est_codigo' => $estacion->getKey(),
    ]);
    $this->assertDatabaseHas('tb_informe_zona', [
        'izo_inf_codigo' => $informe->getKey(),
        'izo_zon_codigo' => $zona->getKey(),
        'izo_distancia_total' => 1200,
        'izo_tiempo_minutos' => 18,
    ]);
    $this->assertDatabaseHas('tb_informe_horario', [
        'iho_inf_codigo' => $informe->getKey(),
        'iho_hor_codigo' => $horario->getKey(),
        'iho_precio' => 250,
    ]);
    $this->assertDatabaseHas('tb_informe_clima', [
        'icl_inf_codigo' => $informe->getKey(),
        'icl_cli_codigo' => $clima->getKey(),
    ]);
    $this->assertDatabaseHas('tb_informe_categoria', [
        'ica_inf_codigo' => $informe->getKey(),
        'ica_cat_codigo' => $categoria->getKey(),
    ]);
});

test('el informe conserva el precio del boleto vigente cuando se generó', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create();
    $horario = Horario::factory()->create([
        'hor_est_codigo_destino' => $estacion->getKey(),
        'hor_precio' => 250,
    ]);
    $informe = app(InformeService::class)->generar($usuario, $estacion->getKey());

    $horario->update(['hor_precio' => 400]);
    $detalle = app(InformeExportacionService::class)->preparar($informe->fresh());

    expect($detalle['trenes'])->toHaveCount(1)
        ->and($detalle['trenes'][0]['precio'])->toBe('250.00')
        ->and($detalle['resumen']['precio_desde'])->toBe(250.0);
});

test('indica que el informe usó un pronóstico no vigente', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create();
    Clima::factory()->for($estacion)->create(['cli_fecha' => today()->subDays(2)]);
    $informe = app(InformeService::class)->generar($usuario, $estacion->getKey());

    $detalle = app(InformeExportacionService::class)->preparar($informe->fresh());

    expect($detalle['clima'])->toHaveCount(1)
        ->and($detalle['climaVigente'])->toBeFalse();
});

test('el historial contiene únicamente los informes del usuario con sus totales', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $otroUsuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $informe = Informe::factory()->create(['inf_usu_codigo' => $usuario->getKey()]);
    $informe->zonas()->attach(ZonaTuristica::factory()->create(), ['izo_distancia_total' => 800, 'izo_tiempo_minutos' => 12]);
    Informe::factory()->create(['inf_usu_codigo' => $otroUsuario->getKey()]);

    $historial = app(InformeService::class)->listar($usuario);

    expect($historial)->toHaveCount(1)
        ->and($historial[0]['zonas'])->toBe(1)
        ->and($historial[0]['trenes'])->toBe(0);
});
