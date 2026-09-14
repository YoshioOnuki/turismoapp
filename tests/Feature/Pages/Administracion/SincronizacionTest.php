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
        'bit_fue_codigo' => FuenteDatos::PeruRail->value,
        'bit_tsi_codigo' => TipoSincronizacion::Manual->value,
        'bit_res_codigo' => ResultadoSincronizacion::Exito->value,
    ]);
    $this->assertDatabaseHas('tb_bitacora', [
        'bit_usu_codigo' => $administrador->getKey(),
        'bit_fue_codigo' => FuenteDatos::Senamhi->value,
        'bit_tsi_codigo' => TipoSincronizacion::Manual->value,
        'bit_res_codigo' => ResultadoSincronizacion::Exito->value,
    ]);
});

test('muestra la última actualización exitosa cuando el intento más reciente falla', function () {
    $this->travelTo(Carbon::parse('2026-09-11 12:00:00'));
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    Bitacora::factory()->create([
        'bit_fue_codigo' => FuenteDatos::PeruRail,
        'bit_res_codigo' => ResultadoSincronizacion::Exito,
        'bit_fecha_inicio' => now()->subDays(2)->subMinute(),
        'bit_fecha_fin' => now()->subDays(2),
        'bit_registros' => 14,
    ]);
    Bitacora::factory()->create([
        'bit_fue_codigo' => FuenteDatos::PeruRail,
        'bit_res_codigo' => ResultadoSincronizacion::Error,
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
        ->shouldReceive('bitacoraReciente')
        ->once()
        ->andReturn([])
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

test('muestra la bitácora con la fecha, la fuente, los registros y el resultado de cada ejecución', function () {
    $this->travelTo(Carbon::parse('2026-09-11 12:00:00'));
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create(['usu_nombre' => 'Rosa Quispe']);
    Bitacora::factory()->create([
        'bit_usu_codigo' => $administrador->getKey(),
        'bit_fue_codigo' => FuenteDatos::PeruRail,
        'bit_tsi_codigo' => TipoSincronizacion::Manual,
        'bit_res_codigo' => ResultadoSincronizacion::Exito,
        'bit_fecha_inicio' => now()->subHours(3)->subMinute(),
        'bit_fecha_fin' => now()->subHours(3),
        'bit_registros' => 14,
        'bit_mensaje' => 'Sincronización completada.',
    ]);
    Bitacora::factory()->create([
        'bit_fue_codigo' => FuenteDatos::Senamhi,
        'bit_tsi_codigo' => TipoSincronizacion::Automatica,
        'bit_res_codigo' => ResultadoSincronizacion::Error,
        'bit_fecha_inicio' => now()->subHour()->subMinute(),
        'bit_fecha_fin' => now()->subHour(),
        'bit_registros' => 0,
        'bit_mensaje' => 'El servicio no respondió.',
    ]);

    Livewire::actingAs($administrador)
        ->test('pages::administracion.sincronizacion')
        ->assertSee('Bitácora de sincronización')
        ->assertSeeInOrder([
            'Bitácora de sincronización',
            '11/09/2026 11:00', 'SENAMHI', 'Automática', 'Error', 'El servicio no respondió.',
            '11/09/2026 09:00', 'PeruRail', 'Manual', '14', 'Éxito', 'Por Rosa Quispe',
        ]);
});
