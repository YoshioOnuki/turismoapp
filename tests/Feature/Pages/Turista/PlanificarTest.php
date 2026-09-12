<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use App\Services\PlanificacionService;
use Illuminate\Support\Facades\Exceptions;
use Livewire\Livewire;

test('el turista selecciona una estación y consulta sus zonas disponibles', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación Central']);
    $categoria = Categoria::factory()->create();
    $turista->preferencias()->attach($categoria);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $categoria->getKey(),
        'zon_nombre' => 'Mirador del Valle',
        'zon_distancia' => 500,
    ]);

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->assertSee('Estación Central')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertHasNoErrors()
        ->assertSee('Mirador del Valle')
        ->assertSee('Mapa del recorrido')
        ->assertSet('estacionConsultada.codigo', $estacion->getKey())
        ->assertSee('Trenes que llegan a la estación')
        ->assertSee('Pronóstico del clima')
        ->call('generarInforme')
        ->assertHasNoErrors()
        ->assertSee('Informe guardado en tu historial.');

    $this->assertDatabaseHas('tb_informe', [
        'inf_usu_codigo' => $turista->getKey(),
        'inf_est_codigo' => $estacion->getKey(),
    ]);
});

test('avisa cuando no hay zonas que coincidan', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create();

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertSee('No encontramos zonas que coincidan')
        ->assertDontSee('Mapa del recorrido');
});

test('rechaza una estación inactiva manipulada desde el cliente', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create(['est_estado' => false]);

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertHasErrors(['estacionCodigo' => 'exists']);
});

test('actualiza el mapa al cambiar de estación y lo retira si no hay coincidencias', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $categoria = Categoria::factory()->create();
    $turista->preferencias()->attach($categoria);
    $primera = Estacion::factory()->create();
    $segunda = Estacion::factory()->create();
    $sinZonas = Estacion::factory()->create();
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $primera->getKey(),
        'zon_cat_codigo' => $categoria->getKey(),
        'zon_nombre' => 'Mirador original',
        'zon_distancia' => 500,
    ]);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $segunda->getKey(),
        'zon_cat_codigo' => $categoria->getKey(),
        'zon_nombre' => 'Museo del destino',
        'zon_distancia' => 700,
    ]);

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('estacionCodigo', (string) $primera->getKey())
        ->call('buscar')
        ->assertSee('Mirador original')
        ->set('estacionCodigo', (string) $segunda->getKey())
        ->call('buscar')
        ->assertSet('estacionConsultada.codigo', $segunda->getKey())
        ->assertSee('Museo del destino')
        ->assertDontSee('Mirador original')
        ->set('estacionCodigo', (string) $sinZonas->getKey())
        ->call('buscar')
        ->assertSet('zonas', [])
        ->assertDontSee('Mapa del recorrido');
});

test('muestra un error seguro y retira los resultados anteriores si la consulta falla', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create();
    $pagina = Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar');
    Exceptions::fake();
    $excepcion = new RuntimeException('Detalle técnico privado');
    $this->mock(PlanificacionService::class)
        ->shouldReceive('zonasDisponibles')
        ->once()
        ->andThrow($excepcion);

    $pagina->call('buscar')
        ->assertSet('busquedaRealizada', false)
        ->assertSet('estacionConsultada', null)
        ->assertHasErrors(['general'])
        ->assertSee('No fue posible consultar la planificación.')
        ->assertDontSee('Detalle técnico privado');

    Exceptions::assertReported(fn (RuntimeException $reportada): bool => $reportada->getMessage() === 'Detalle técnico privado');
});
