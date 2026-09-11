<?php

use App\Enums\TipoPerfil;
use App\Exceptions\Autenticacion\CredencialesInvalidasException;
use App\Exceptions\Autenticacion\DemasiadosIntentosException;
use App\Models\Usuario;
use App\Services\AutenticacionService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

test('registra al turista con el perfil de usuario final y le abre la sesión', function () {
    Event::fake([Registered::class]);

    $usuario = app(AutenticacionService::class)->registrar('Ana Quispe', 'ana@turismoapp.test', 'clave-segura-123');

    expect($usuario->tienePerfil(TipoPerfil::UsuarioFinal))->toBeTrue();
    expect(Hash::check('clave-segura-123', $usuario->usu_clave))->toBeTrue();
    $this->assertDatabaseHas('tb_usuario', ['usu_nombre' => 'Ana Quispe', 'usu_correo' => 'ana@turismoapp.test']);
    $this->assertAuthenticatedAs($usuario);
    Event::assertDispatched(Registered::class);
});

test('abre la sesión de un usuario activo y lo devuelve', function () {
    $usuario = Usuario::factory()->create(['usu_correo' => 'ana@turismoapp.test', 'usu_clave' => 'clave-segura-123']);

    $autenticado = app(AutenticacionService::class)->iniciarSesion('ana@turismoapp.test', 'clave-segura-123', false, '127.0.0.1');

    expect($autenticado->is($usuario))->toBeTrue();
    $this->assertAuthenticatedAs($usuario);
});

test('rechaza una clave incorrecta sin abrir la sesión', function () {
    Usuario::factory()->create(['usu_correo' => 'ana@turismoapp.test', 'usu_clave' => 'clave-segura-123']);

    expect(fn () => app(AutenticacionService::class)->iniciarSesion('ana@turismoapp.test', 'otra-clave', false, '127.0.0.1'))
        ->toThrow(CredencialesInvalidasException::class);
    $this->assertGuest();
});

test('rechaza a un usuario dado de baja aunque su clave sea correcta', function () {
    Usuario::factory()->inactivo()->create(['usu_correo' => 'ana@turismoapp.test', 'usu_clave' => 'clave-segura-123']);

    expect(fn () => app(AutenticacionService::class)->iniciarSesion('ana@turismoapp.test', 'clave-segura-123', false, '127.0.0.1'))
        ->toThrow(CredencialesInvalidasException::class);
    $this->assertGuest();
});

test('bloquea el acceso durante un minuto tras cinco intentos fallidos', function () {
    $this->freezeTime();
    Usuario::factory()->create(['usu_correo' => 'ana@turismoapp.test', 'usu_clave' => 'clave-segura-123']);
    $autenticacion = app(AutenticacionService::class);
    Collection::times(5, fn () => rescue(
        fn () => $autenticacion->iniciarSesion('ana@turismoapp.test', 'otra-clave', false, '127.0.0.1'),
        report: false,
    ));

    expect(fn () => $autenticacion->iniciarSesion('ana@turismoapp.test', 'clave-segura-123', false, '127.0.0.1'))
        ->toThrow(fn (DemasiadosIntentosException $excepcion) => expect($excepcion->segundos)->toBe(60));
    $this->assertGuest();
});

test('reinicia el conteo de intentos después de un inicio de sesión exitoso', function () {
    $usuario = Usuario::factory()->create(['usu_correo' => 'ana@turismoapp.test', 'usu_clave' => 'clave-segura-123']);
    $autenticacion = app(AutenticacionService::class);
    $intentoFallido = fn () => rescue(
        fn () => $autenticacion->iniciarSesion('ana@turismoapp.test', 'otra-clave', false, '127.0.0.1'),
        report: false,
    );
    Collection::times(4, $intentoFallido);
    $autenticacion->iniciarSesion('ana@turismoapp.test', 'clave-segura-123', false, '127.0.0.1');
    $autenticacion->cerrarSesion();
    $intentoFallido();

    $autenticacion->iniciarSesion('ana@turismoapp.test', 'clave-segura-123', false, '127.0.0.1');

    $this->assertAuthenticatedAs($usuario);
});

test('cierra la sesión del usuario', function () {
    $this->actingAs(Usuario::factory()->create());

    app(AutenticacionService::class)->cerrarSesion();

    $this->assertGuest();
});
