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

test('el turista abre su informe consolidado como un pdf válido en el navegador', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación Central']);
    $categoria = Categoria::factory()->create(['cat_nombre' => 'Museos']);
    $informe = Informe::factory()->create([
        'inf_usu_codigo' => $turista->getKey(),
        'inf_est_codigo' => $estacion->getKey(),
    ]);
    $informe->categorias()->attach($categoria);
    $informe->zonas()->attach(ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $categoria->getKey(),
        'zon_nombre' => 'Museo Ferroviario',
    ]), ['izo_distancia_total' => 1600, 'izo_tiempo_minutos' => 24]);
    $informe->horarios()->attach(Horario::factory()->create(['hor_est_codigo_destino' => $estacion->getKey()]), ['iho_precio' => 45]);
    $informe->climas()->attach(Clima::factory()->for($estacion)->create());

    $respuesta = $this->actingAs($turista)->get(route('turista.informes.pdf', $informe));

    $respuesta->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'inline; filename=informe-'.$informe->getKey().'.pdf');
    expect($respuesta->getContent())->toStartWith('%PDF-')->toContain('%%EOF');
});

test('abre un informe anterior aunque no tenga zonas trenes ni clima', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $informe = Informe::factory()->create(['inf_usu_codigo' => $turista->getKey()]);

    $this->actingAs($turista)->get(route('turista.informes.pdf', $informe))
        ->assertOk()
        ->assertHeader('content-disposition', 'inline; filename=informe-'.$informe->getKey().'.pdf');
});

test('un invitado debe iniciar sesión antes de descargar un pdf', function () {
    $informe = Informe::factory()->create();

    $this->get(route('turista.informes.pdf', $informe))->assertRedirect(route('login'));
});

test('un turista no puede descargar el pdf de otro usuario', function () {
    $informe = Informe::factory()->create();
    $otroTurista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();

    $this->actingAs($otroTurista)->get(route('turista.informes.pdf', $informe))->assertForbidden();
});

test('un perfil interno no puede descargar informes de turistas en pdf', function (TipoPerfil $perfil) {
    $informe = Informe::factory()->create();
    $usuario = Usuario::factory()->conPerfil($perfil)->create();

    $this->actingAs($usuario)->get(route('turista.informes.pdf', $informe))->assertForbidden();
})->with([TipoPerfil::TravelGroup, TipoPerfil::AdministradorMtc]);

test('devuelve no encontrado para un informe pdf inexistente', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();

    $this->actingAs($turista)->get(route('turista.informes.pdf', 999))->assertNotFound();
});

test('escapa los textos del informe antes de convertirlos a pdf', function () {
    $estacion = Estacion::factory()->create(['est_nombre' => '<script>alert(1)</script>']);
    $informe = Informe::factory()->create(['inf_est_codigo' => $estacion->getKey()]);
    $informe->zonas()->attach(ZonaTuristica::factory()->create([
        'zon_nombre' => '<img src="file:///etc/passwd">',
    ]), ['izo_distancia_total' => 800, 'izo_tiempo_minutos' => 12]);

    $html = view('informes.html', [...app(InformeExportacionService::class)->preparar($informe), 'pdf' => true])->render();

    expect($html)->toContain('&lt;script&gt;', '&lt;img')
        ->not->toContain('<script>', '<img src=');
});
