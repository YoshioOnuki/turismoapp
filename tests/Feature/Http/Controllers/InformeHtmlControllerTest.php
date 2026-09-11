<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Usuario;

test('el turista descarga su informe como un documento html', function () {
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
                'dificultad' => 'Fácil',
            ]],
            'trenes' => [[
                'origen' => 'Cusco',
                'servicio' => 'Expreso',
                'salida' => '08:00',
                'llegada' => '09:30',
                'precio' => '45.00',
            ]],
            'clima' => [[
                'fecha' => '11 sep',
                'descripcion' => 'Despejado',
                'minima' => '8.0',
                'maxima' => '20.0',
                'lluvia' => 10,
            ]],
        ],
    ]);
    $informe->categorias()->attach($categoria);

    $this->actingAs($turista)
        ->get(route('turista.informes.html', $informe))
        ->assertOk()
        ->assertHeader('content-type', 'text/html; charset=UTF-8')
        ->assertHeader('content-disposition', 'attachment; filename="informe-'.$informe->getKey().'.html"')
        ->assertSeeInOrder(['Estación Central', 'Museos', 'Museo Ferroviario', 'Expreso', 'Despejado']);
});

test('un turista no puede exportar el informe de otro usuario', function () {
    $propietario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $otroTurista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $informe = Informe::factory()->create(['inf_usu_codigo' => $propietario->getKey()]);

    $this->actingAs($otroTurista)
        ->get(route('turista.informes.html', $informe))
        ->assertForbidden();
});
