<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;
use App\Services\AutenticacionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Exceptions;
use Livewire\Livewire;

test('muestra la página de inicio de sesión a los invitados', function () {
    $this->get(route('login'))->assertSeeLivewire('pages::auth.iniciar-sesion');
});

test('lleva a su panel a quien ya inició sesión', function () {
    $this->actingAs(Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create())
        ->get(route('login'))
        ->assertRedirectToRoute('travel-group.panel');
});

test('inicia la sesión y lleva a cada usuario al panel de su perfil', function (TipoPerfil $perfil, string $panel) {
    $usuario = Usuario::factory()->conPerfil($perfil)->create([
        'usu_correo' => 'ana@turismoapp.test',
        'usu_clave' => 'clave-segura-123',
    ]);

    Livewire::test('pages::auth.iniciar-sesion')
        ->set(['correo' => 'ana@turismoapp.test', 'clave' => 'clave-segura-123'])
        ->call('iniciarSesion')
        ->assertRedirectToRoute($panel);

    $this->assertAuthenticatedAs($usuario);
})->with([
    'usuario final' => [TipoPerfil::UsuarioFinal, 'turista.panel'],
    'Travel Group Perú' => [TipoPerfil::TravelGroup, 'travel-group.panel'],
    'administrador MTC' => [TipoPerfil::AdministradorMtc, 'administracion.panel'],
]);

test('acepta el correo con mayúsculas o espacios alrededor', function () {
    $usuario = Usuario::factory()->create(['usu_correo' => 'ana@turismoapp.test', 'usu_clave' => 'clave-segura-123']);

    Livewire::test('pages::auth.iniciar-sesion')
        ->set(['correo' => '  Ana@TurismoApp.TEST ', 'clave' => 'clave-segura-123'])
        ->call('iniciarSesion');

    $this->assertAuthenticatedAs($usuario);
});

test('avisa que las credenciales no coinciden sin abrir la sesión', function () {
    Usuario::factory()->create(['usu_correo' => 'ana@turismoapp.test', 'usu_clave' => 'clave-segura-123']);

    Livewire::test('pages::auth.iniciar-sesion')
        ->set(['correo' => 'ana@turismoapp.test', 'clave' => 'otra-clave'])
        ->call('iniciarSesion')
        ->assertHasErrors('correo')
        ->assertSee('Estas credenciales no coinciden con nuestros registros.')
        ->assertNoRedirect();

    $this->assertGuest();
});

test('indica cuánto esperar después de cinco intentos fallidos', function () {
    $this->freezeTime();
    Usuario::factory()->create(['usu_correo' => 'ana@turismoapp.test', 'usu_clave' => 'clave-segura-123']);
    $componente = Livewire::test('pages::auth.iniciar-sesion')
        ->set(['correo' => 'ana@turismoapp.test', 'clave' => 'otra-clave']);
    Collection::times(5, fn () => $componente->call('iniciarSesion'));

    $componente->call('iniciarSesion');

    $componente->assertSee('Demasiados intentos de acceso. Por favor inténtelo de nuevo en 60 segundos.');
});

test('exige el correo y la contraseña', function () {
    Livewire::test('pages::auth.iniciar-sesion')
        ->call('iniciarSesion')
        ->assertHasErrors(['correo' => 'required', 'clave' => 'required'])
        ->assertSee('El campo correo electrónico es obligatorio.')
        ->assertSee('El campo contraseña es obligatorio.');
});

test('muestra un mensaje general y registra el error si el servicio falla', function () {
    Exceptions::fake();
    $this->mock(AutenticacionService::class)
        ->shouldReceive('iniciarSesion')
        ->andThrow(new RuntimeException('Base de datos no disponible'));

    Livewire::test('pages::auth.iniciar-sesion')
        ->set(['correo' => 'ana@turismoapp.test', 'clave' => 'clave-segura-123'])
        ->call('iniciarSesion')
        ->assertSee('No pudimos iniciar tu sesión. Inténtalo de nuevo en unos minutos.')
        ->assertDontSee('Base de datos no disponible')
        ->assertNoRedirect();

    Exceptions::assertReported(RuntimeException::class);
});
