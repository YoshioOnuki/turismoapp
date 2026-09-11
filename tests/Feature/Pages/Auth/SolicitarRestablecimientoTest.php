<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;
use App\Services\AutenticacionService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('muestra la página para solicitar el enlace a los invitados', function () {
    $this->get(route('password.request'))
        ->assertSeeLivewire('pages::auth.solicitar-restablecimiento');
});

test('lleva a su panel a quien ya inició sesión', function () {
    $this->actingAs(Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create())
        ->get(route('password.request'))
        ->assertRedirectToRoute('turista.panel');
});

test('envía el enlace de recuperación a una cuenta activa', function () {
    $usuario = Usuario::factory()->create(['usu_correo' => 'ana@turismoapp.test']);
    Notification::fake();

    Livewire::test('pages::auth.solicitar-restablecimiento')
        ->set('correo', '  Ana@TurismoApp.TEST ')
        ->call('solicitarRestablecimiento')
        ->assertSet('correo', 'ana@turismoapp.test')
        ->assertSet('enviado', true)
        ->assertSee('Si existe una cuenta activa con ese correo, recibirás un enlace de recuperación.');

    Notification::assertSentTo(
        $usuario,
        ResetPassword::class,
        fn (ResetPassword $notificacion) => $notificacion->toMail($usuario)->subject === 'Restablece tu contraseña',
    );
});

test('no revela si el correo no pertenece a una cuenta', function () {
    Notification::fake();

    Livewire::test('pages::auth.solicitar-restablecimiento')
        ->set('correo', 'desconocido@turismoapp.test')
        ->call('solicitarRestablecimiento')
        ->assertSet('enviado', true)
        ->assertSee('Si existe una cuenta activa con ese correo, recibirás un enlace de recuperación.')
        ->assertHasNoErrors();

    Notification::assertNothingSent();
});

test('no envía enlaces a cuentas inactivas', function () {
    Usuario::factory()->create([
        'usu_correo' => 'inactivo@turismoapp.test',
        'usu_estado' => false,
    ]);
    Notification::fake();

    Livewire::test('pages::auth.solicitar-restablecimiento')
        ->set('correo', 'inactivo@turismoapp.test')
        ->call('solicitarRestablecimiento')
        ->assertSet('enviado', true);

    Notification::assertNothingSent();
});

test('exige un correo válido', function () {
    Livewire::test('pages::auth.solicitar-restablecimiento')
        ->set('correo', 'correo-invalido')
        ->call('solicitarRestablecimiento')
        ->assertHasErrors(['correo' => 'email'])
        ->assertSee('El campo correo electrónico debe ser una dirección de correo válida.');
});

test('muestra un mensaje general y registra el error si el servicio falla', function () {
    Exceptions::fake();
    $this->mock(AutenticacionService::class)
        ->shouldReceive('solicitarRestablecimiento')
        ->once()
        ->andThrow(new RuntimeException('Servicio de correo no disponible'));

    Livewire::test('pages::auth.solicitar-restablecimiento')
        ->set('correo', 'ana@turismoapp.test')
        ->call('solicitarRestablecimiento')
        ->assertSee('No pudimos enviar el enlace. Inténtalo de nuevo en unos minutos.')
        ->assertDontSee('Servicio de correo no disponible')
        ->assertSet('enviado', false);

    Exceptions::assertReported(RuntimeException::class);
});
