<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Usuario;
use App\Services\InformeExportacionService;

test('el turista descarga su informe consolidado como un pdf válido', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación Central']);
    $categoria = Categoria::factory()->create(['cat_nombre' => 'Museos']);
    $informe = Informe::factory()->create([
        'inf_usu_codigo' => $turista->getKey(),
        'inf_est_codigo' => $estacion->getKey(),
        'inf_contenido' => [
            'zonas' => [[
                'nombre' => 'Museo Ferroviario',
                'categoria' => 'Museos',
                'distancia_total' => 1600,
                'tiempo_minutos' => 24,
                'dificultad' => 'baja',
            ]],
            'trenes' => [[
                'origen' => 'Cusco', 'servicio' => 'Expreso',
                'salida' => '08:00', 'llegada' => '09:30', 'precio' => '45.00',
            ]],
            'clima' => [[
                'fecha' => '12 sep', 'descripcion' => 'Despejado',
                'minima' => '8.0', 'maxima' => '20.0', 'lluvia' => 10,
            ]],
        ],
    ]);
    $informe->categorias()->attach($categoria);

    $respuesta = $this->actingAs($turista)->get(route('turista.informes.pdf', $informe));

    $respuesta->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('informe-'.$informe->getKey().'.pdf');
    expect($respuesta->getContent())->toStartWith('%PDF-')->toContain('%%EOF');
});

test('exporta un informe anterior aunque no tenga zonas trenes ni clima', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $informe = Informe::factory()->create(['inf_usu_codigo' => $turista->getKey()]);

    $this->actingAs($turista)->get(route('turista.informes.pdf', $informe))
        ->assertOk()
        ->assertDownload('informe-'.$informe->getKey().'.pdf');
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
    $informe = Informe::factory()->create([
        'inf_est_codigo' => $estacion->getKey(),
        'inf_contenido' => [
            'zonas' => [['nombre' => '<img src="file:///etc/passwd">']],
            'trenes' => [],
            'clima' => [],
        ],
    ]);

    $html = view('informes.html', [...app(InformeExportacionService::class)->preparar($informe), 'pdf' => true])->render();

    expect($html)->toContain('&lt;script&gt;', '&lt;img')
        ->not->toContain('<script>', '<img src=');
});
