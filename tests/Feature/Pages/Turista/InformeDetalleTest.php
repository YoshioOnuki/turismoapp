<?php

use App\Enums\Dificultad;
use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Clima;
use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Informe;
use App\Models\Usuario;
use App\Models\ZonaTuristica;

test('el turista consulta el detalle completo de su informe', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create([
        'est_nombre' => 'Estación Machu Picchu',
        'est_latitud' => -13.1547,
        'est_longitud' => -72.5252,
    ]);
    $categoria = Categoria::factory()->create(['cat_nombre' => 'Naturaleza']);
    $zona = ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $categoria->getKey(),
        'zon_nombre' => 'Mariposario de Machu Picchu',
        'zon_descripcion' => 'Refugio de mariposas nativas junto al río.',
        'zon_latitud' => -13.1601,
        'zon_longitud' => -72.5327,
        'zon_distancia' => 1300,
        'zon_dif_codigo' => Dificultad::Baja,
    ]);
    $horario = Horario::factory()->conServicio('Expedition')->create([
        'hor_est_codigo_origen' => Estacion::factory()->create(['est_nombre' => 'Estación Ollantaytambo'])->getKey(),
        'hor_est_codigo_destino' => $estacion->getKey(),
        'hor_hora_salida' => '06:10:00',
        'hor_hora_llegada' => '07:40:00',
        'hor_precio' => 250,
    ]);
    $clima = Clima::factory()->for($estacion)->create(['cli_descripcion' => 'Lluvias aisladas']);
    $informe = Informe::factory()->create([
        'inf_usu_codigo' => $turista->getKey(),
        'inf_est_codigo' => $estacion->getKey(),
    ]);
    $informe->categorias()->attach($categoria);
    $informe->zonas()->attach($zona, ['izo_distancia_total' => 2600, 'izo_tiempo_minutos' => 39]);
    $informe->horarios()->attach($horario, ['iho_precio' => 250]);
    $informe->climas()->attach($clima);

    $this->actingAs($turista)
        ->get(route('turista.informes.detalle', $informe))
        ->assertSeeInOrder([
            'Tu visita desde Estación Machu Picchu',
            'Naturaleza',
            'Mariposario de Machu Picchu',
            '2,6 km',
            'Expedition',
            '1 h 30 min',
            'Lluvias aisladas',
        ])
        ->assertSee(route('turista.informes.pdf', $informe))
        ->assertSee('target="_blank"', false)
        ->assertSee('Abrir PDF')
        ->assertDontSee('Descargar HTML');
});

test('un invitado debe iniciar sesión antes de ver un informe', function () {
    $informe = Informe::factory()->create();

    $this->get(route('turista.informes.detalle', $informe))->assertRedirect(route('login'));
});

test('un turista no puede ver el informe de otro usuario', function () {
    $propietario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $otroTurista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $informe = Informe::factory()->create(['inf_usu_codigo' => $propietario->getKey()]);

    $this->actingAs($otroTurista)
        ->get(route('turista.informes.detalle', $informe))
        ->assertForbidden();
});

test('un perfil interno no puede ver un informe turístico', function (TipoPerfil $perfil) {
    $informe = Informe::factory()->create();
    $usuario = Usuario::factory()->conPerfil($perfil)->create();

    $this->actingAs($usuario)
        ->get(route('turista.informes.detalle', $informe))
        ->assertForbidden();
})->with([TipoPerfil::TravelGroup, TipoPerfil::AdministradorMtc]);

test('devuelve no encontrado para un informe inexistente', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();

    $this->actingAs($turista)
        ->get(route('turista.informes.detalle', 999))
        ->assertNotFound();
});

test('escapa el contenido guardado al mostrar el detalle', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $informe = Informe::factory()->create(['inf_usu_codigo' => $turista->getKey()]);
    $informe->zonas()->attach(ZonaTuristica::factory()->create([
        'zon_nombre' => '<script>alert("xss")</script>',
        'zon_descripcion' => '<img src=x onerror=alert(1)>',
    ]), ['izo_distancia_total' => 800, 'izo_tiempo_minutos' => 12]);

    $this->actingAs($turista)
        ->get(route('turista.informes.detalle', $informe))
        ->assertSee('&lt;script&gt;', false)
        ->assertSee('&lt;img', false)
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertDontSee('<img src=x', false);
});
