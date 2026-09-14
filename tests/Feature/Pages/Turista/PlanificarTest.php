<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Clima;
use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Servicio;
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

    $pagina = Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->assertSee('Estación Central')
        ->assertSet('categoriasSeleccionadas', [$categoria->getKey()])
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

    $informe = Informe::query()->sole();
    expect($informe->inf_usu_codigo)->toBe($turista->getKey())
        ->and($informe->inf_est_codigo)->toBe($estacion->getKey());
    $pagina->assertSet('informeCodigo', $informe->getKey())
        ->assertSee(route('turista.informes.detalle', $informe))
        ->assertSee(route('turista.informes.pdf', $informe))
        ->assertDontSee('Descargar HTML');
});

test('guarda como preferencias las categorías marcadas al buscar', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create();
    $anterior = Categoria::factory()->create();
    $nueva = Categoria::factory()->create();
    $turista->preferencias()->attach($anterior);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $nueva->getKey(),
        'zon_nombre' => 'Zona de la nueva categoría',
        'zon_distancia' => 400,
    ]);

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('categoriasSeleccionadas', [(string) $nueva->getKey()])
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertHasNoErrors()
        ->assertSee('Zona de la nueva categoría');

    expect($turista->preferencias()->pluck('tb_categoria.cat_codigo')->all())->toBe([$nueva->getKey()]);
});

test('exige marcar al menos una categoría antes de buscar', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create();

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertHasErrors(['categoriasSeleccionadas' => 'required'])
        ->assertSee('Marca al menos una categoría turística.')
        ->assertSet('busquedaRealizada', false);
});

test('avisa cuando no hay zonas que coincidan', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $turista->preferencias()->attach(Categoria::factory()->create());
    $estacion = Estacion::factory()->create();

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertSee('No encontramos zonas que coincidan')
        ->assertSee('Amplía tus criterios')
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

test('muestra el tiempo de viaje del tren y la fecha de actualización de los datos', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $turista->preferencias()->attach(Categoria::factory()->create());
    $estacion = Estacion::factory()->create();
    $estacion->horariosDeLlegada()->create([
        'hor_codigo_externo' => 'TREN-81',
        'hor_est_codigo_origen' => Estacion::factory()->create()->getKey(),
        'hor_ser_codigo' => Servicio::factory()->create(['ser_nombre' => 'Expedition'])->getKey(),
        'hor_hora_salida' => '06:10:00',
        'hor_hora_llegada' => '07:40:00',
        'hor_precio' => 250,
    ]);

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertSee('1 h 30 min de viaje')
        ->assertSee('Datos de PeruRail actualizados al');
});

test('muestra el último pronóstico disponible cuando no hay uno vigente', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $turista->preferencias()->attach(Categoria::factory()->create());
    $estacion = Estacion::factory()->create();
    Clima::factory()->create([
        'cli_est_codigo' => $estacion->getKey(),
        'cli_fecha' => today()->subDays(2),
        'cli_descripcion' => 'Lluvias aisladas',
    ]);

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertSet('climaVigente', false)
        ->assertSee('No hay un pronóstico vigente para esta estación.')
        ->assertSee('Lluvias aisladas')
        ->assertSee('Pronóstico del SENAMHI actualizado al');
});

test('no guarda un informe sin haber buscado una estación', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->call('generarInforme')
        ->assertHasErrors(['general'])
        ->assertSee('Primero busca las zonas de una estación.');

    $this->assertDatabaseCount('tb_informe', 0);
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
    $turista->preferencias()->attach(Categoria::factory()->create());
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
