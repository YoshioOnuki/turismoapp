<?php

use App\Enums\FuenteDatos;
use App\Enums\ResultadoSincronizacion;
use App\Enums\TipoPerfil;
use App\Enums\TipoSincronizacion;
use App\Models\Bitacora;
use App\Models\Usuario;
use App\Services\SincronizacionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Exceptions;
use Livewire\Livewire;

test('el administrador puede consultar el estado de las fuentes', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();

    $this->actingAs($administrador)
        ->get(route('administracion.sincronizacion'))
        ->assertOk()
        ->assertSeeInOrder(['Sincronización de datos', 'PeruRail', 'SENAMHI']);
});

test('un usuario sin perfil administrador no puede consultar la sincronización', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();

    $this->actingAs($usuario)
        ->get(route('administracion.sincronizacion'))
        ->assertForbidden();
});

test('el administrador ejecuta una sincronización manual', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();

    Livewire::actingAs($administrador)
        ->test('pages::administracion.sincronizacion')
        ->call('sincronizar')
        ->assertHasNoErrors()
        ->assertSee('Las fuentes se sincronizaron correctamente.')
        ->assertSee('Actualizada');

    $this->assertDatabaseCount('tb_bitacora', 2);
    $this->assertDatabaseHas('tb_bitacora', [
        'bit_usu_codigo' => $administrador->getKey(),
        'bit_fuente' => FuenteDatos::PeruRail->value,
        'bit_tipo' => TipoSincronizacion::Manual->value,
        'bit_resultado' => ResultadoSincronizacion::Exito->value,
    ]);
    $this->assertDatabaseHas('tb_bitacora', [
        'bit_usu_codigo' => $administrador->getKey(),
        'bit_fuente' => FuenteDatos::Senamhi->value,
        'bit_tipo' => TipoSincronizacion::Manual->value,
        'bit_resultado' => ResultadoSincronizacion::Exito->value,
    ]);
});

test('muestra la última actualización exitosa cuando el intento más reciente falla', function () {
    $this->travelTo(Carbon::parse('2026-09-11 12:00:00'));
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    Bitacora::factory()->create([
        'bit_fuente' => FuenteDatos::PeruRail,
        'bit_resultado' => ResultadoSincronizacion::Exito,
        'bit_fecha_inicio' => now()->subDays(2)->subMinute(),
        'bit_fecha_fin' => now()->subDays(2),
        'bit_registros' => 14,
    ]);
    Bitacora::factory()->create([
        'bit_fuente' => FuenteDatos::PeruRail,
        'bit_resultado' => ResultadoSincronizacion::Error,
        'bit_fecha_inicio' => now()->subHour()->subMinute(),
        'bit_fecha_fin' => now()->subHour(),
        'bit_registros' => 0,
    ]);

    Livewire::actingAs($administrador)
        ->test('pages::administracion.sincronizacion')
        ->assertSee('Con incidencia')
        ->assertSee('09/09/2026 12:00')
        ->assertSee('Se conservan los datos de la última actualización exitosa.');
});

test('muestra un mensaje seguro cuando ocurre un error inesperado', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    Exceptions::fake();
    $this->mock(SincronizacionService::class)
        ->shouldReceive('estadoFuentes')
        ->once()
        ->andReturn([
            'perurail' => [
                'nombre' => 'PeruRail',
                'resultado' => null,
                'ultima_ejecucion' => null,
                'ultima_actualizacion' => null,
                'registros' => 0,
            ],
            'senamhi' => [
                'nombre' => 'SENAMHI',
                'resultado' => null,
                'ultima_ejecucion' => null,
                'ultima_actualizacion' => null,
                'registros' => 0,
            ],
        ])
        ->shouldReceive('ejecutar')
        ->once()
        ->andThrow(new RuntimeException('Detalle interno'));

    Livewire::actingAs($administrador)
        ->test('pages::administracion.sincronizacion')
        ->call('sincronizar')
        ->assertHasErrors(['general'])
        ->assertSee('No fue posible iniciar la sincronización. Inténtalo nuevamente.')
        ->assertDontSee('Detalle interno');

    Exceptions::assertReported(RuntimeException::class);
});
