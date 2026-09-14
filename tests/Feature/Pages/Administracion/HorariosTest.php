<?php

use App\Enums\TipoPerfil;
use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\HorarioService;
use Illuminate\Support\Facades\Exceptions;
use Livewire\Livewire;

test('el administrador consulta los horarios con su tiempo de viaje y precio', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    Horario::factory()->conServicio('Vistadome')->create([
        'hor_hora_salida' => '07:05:00',
        'hor_hora_llegada' => '08:35:00',
        'hor_precio' => 310,
    ]);

    $this->actingAs($administrador)
        ->get(route('administracion.horarios'))
        ->assertOk()
        ->assertSee('Horarios y precios')
        ->assertSee('Vistadome')
        ->assertSee('1 h 30 min de viaje')
        ->assertSee('S/ 310.00');
});

test('solo el administrador MTC accede a los horarios', function (TipoPerfil $perfil) {
    $this->actingAs(Usuario::factory()->conPerfil($perfil)->create())
        ->get(route('administracion.horarios'))
        ->assertForbidden();
})->with([
    'usuario final' => [TipoPerfil::UsuarioFinal],
    'Travel Group Perú' => [TipoPerfil::TravelGroup],
]);

test('registra un horario adicional', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    $origen = Estacion::factory()->create();
    $destino = Estacion::factory()->create();

    Livewire::actingAs($administrador)
        ->test('pages::administracion.horarios')
        ->call('crear')
        ->set([
            'origenCodigo' => (string) $origen->getKey(),
            'destinoCodigo' => (string) $destino->getKey(),
            'servicio' => ' Expedition ',
            'horaSalida' => '06:10',
            'horaLlegada' => '07:40',
            'precio' => '250.50',
        ])
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('mostrarFormulario', false)
        ->assertSee('Horario registrado.')
        ->assertSee('1 h 30 min de viaje');

    $this->assertDatabaseHas('tb_horario', [
        'hor_est_codigo_origen' => $origen->getKey(),
        'hor_est_codigo_destino' => $destino->getKey(),
        'hor_ser_codigo' => Servicio::query()->where('ser_nombre', 'Expedition')->value('ser_codigo'),
        'hor_hora_salida' => '06:10:00',
        'hor_hora_llegada' => '07:40:00',
        'hor_precio' => 250.50,
        'hor_estado' => true,
    ]);
});

test('valida los datos del horario', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    $estacion = Estacion::factory()->create();

    Livewire::actingAs($administrador)
        ->test('pages::administracion.horarios')
        ->set([
            'origenCodigo' => (string) $estacion->getKey(),
            'destinoCodigo' => (string) $estacion->getKey(),
            'horaSalida' => '25:00',
            'precio' => '-5',
        ])
        ->call('guardar')
        ->assertHasErrors([
            'destinoCodigo' => 'different',
            'servicio' => 'required',
            'horaSalida' => 'date_format',
            'horaLlegada' => 'required',
            'precio' => 'min',
        ]);

    $this->assertDatabaseCount('tb_horario', 0);
});

test('corrige el precio de un horario y lo da de baja sin eliminarlo', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    $horario = Horario::factory()->create(['hor_precio' => 300]);

    Livewire::actingAs($administrador)
        ->test('pages::administracion.horarios')
        ->call('editar', $horario->getKey())
        ->assertSet('precio', '300.00')
        ->set('precio', '280')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSee('Horario actualizado.')
        ->call('cambiarEstado', $horario->getKey())
        ->assertSee('Horario dado de baja.');

    expect($horario->fresh())
        ->hor_precio->toBe('280.00')
        ->hor_estado->toBeFalse();
    $this->assertDatabaseCount('tb_horario', 1);
});

test('muestra un mensaje seguro si no puede guardar el horario', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    $origen = Estacion::factory()->create();
    $destino = Estacion::factory()->create();
    $pagina = Livewire::actingAs($administrador)->test('pages::administracion.horarios');
    Exceptions::fake();
    $this->mock(HorarioService::class)
        ->shouldReceive('guardar')
        ->once()
        ->andThrow(new RuntimeException('Detalle interno'));

    $pagina->set([
        'origenCodigo' => (string) $origen->getKey(),
        'destinoCodigo' => (string) $destino->getKey(),
        'servicio' => 'Expedition',
        'horaSalida' => '06:10',
        'horaLlegada' => '07:40',
        'precio' => '250',
    ])
        ->call('guardar')
        ->assertHasErrors(['general'])
        ->assertSee('No fue posible guardar el horario. Inténtalo nuevamente.')
        ->assertDontSee('Detalle interno');

    Exceptions::assertReported(RuntimeException::class);
});
