<?php

use App\Models\Usuario;
use App\Services\AutenticacionService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

test('muestra la página recibida desde el enlace de recuperación', function () {
    $this->get(route('password.reset', [
        'token' => 'token-de-prueba',
        'email' => 'ana@turismoapp.test',
    ]))->assertSeeLivewire('pages::auth.restablecer-clave');
});

test('restablece la contraseña con un token válido y permite iniciar sesión', function () {
    $usuario = Usuario::factory()->create([
        'usu_correo' => 'ana@turismoapp.test',
        'usu_clave' => 'clave-anterior-123',
    ]);
    $token = Password::broker()->createToken($usuario);
    Event::fake([PasswordReset::class]);

    Livewire::test('pages::auth.restablecer-clave', ['token' => $token])
        ->set([
            'correo' => 'ANA@turismoapp.test',
            'clave' => 'clave-nueva-123',
            'clave_confirmation' => 'clave-nueva-123',
        ])
        ->call('restablecerClave')
        ->assertRedirectToRoute('login');

    expect(Hash::check('clave-nueva-123', $usuario->fresh()->usu_clave))->toBeTrue();
    expect(session('estado'))->toBe('Tu contraseña se restableció correctamente. Ya puedes iniciar sesión.');
    $this->assertDatabaseEmpty('password_reset_tokens');
    Event::assertDispatched(PasswordReset::class, fn (PasswordReset $evento) => $evento->user->is($usuario));
});

test('rechaza un token inválido sin cambiar la contraseña', function () {
    $usuario = Usuario::factory()->create([
        'usu_correo' => 'ana@turismoapp.test',
        'usu_clave' => 'clave-anterior-123',
    ]);

    Livewire::test('pages::auth.restablecer-clave', ['token' => 'token-invalido'])
        ->set([
            'correo' => 'ana@turismoapp.test',
            'clave' => 'clave-nueva-123',
            'clave_confirmation' => 'clave-nueva-123',
        ])
        ->call('restablecerClave')
        ->assertHasErrors('correo')
        ->assertSee('El enlace de recuperación es inválido o ha expirado.')
        ->assertNoRedirect();

    expect(Hash::check('clave-anterior-123', $usuario->fresh()->usu_clave))->toBeTrue();
});

test('exige correo y contraseña', function () {
    Livewire::test('pages::auth.restablecer-clave', ['token' => 'token-de-prueba'])
        ->call('restablecerClave')
        ->assertHasErrors(['correo' => 'required', 'clave' => 'required'])
        ->assertSee('El campo correo electrónico es obligatorio.')
        ->assertSee('El campo contraseña es obligatorio.');
});

test('exige que la confirmación coincida con la contraseña', function () {
    Livewire::test('pages::auth.restablecer-clave', ['token' => 'token-de-prueba'])
        ->set([
            'correo' => 'ana@turismoapp.test',
            'clave' => 'clave-nueva-123',
            'clave_confirmation' => 'otra-clave',
        ])
        ->call('restablecerClave')
        ->assertHasErrors(['clave' => 'confirmed'])
        ->assertSee('El campo confirmación de contraseña no coincide.');
});

test('exige que la contraseña tenga al menos ocho caracteres', function () {
    Livewire::test('pages::auth.restablecer-clave', ['token' => 'token-de-prueba'])
        ->set([
            'correo' => 'ana@turismoapp.test',
            'clave' => 'corta12',
            'clave_confirmation' => 'corta12',
        ])
        ->call('restablecerClave')
        ->assertHasErrors('clave')
        ->assertSee('El campo contraseña debe contener al menos 8 caracteres.');
});

test('muestra un mensaje general y registra el error si el servicio falla', function () {
    Exceptions::fake();
    $this->mock(AutenticacionService::class)
        ->shouldReceive('restablecerClave')
        ->once()
        ->andThrow(new RuntimeException('Base de datos no disponible'));

    Livewire::test('pages::auth.restablecer-clave', ['token' => 'token-de-prueba'])
        ->set([
            'correo' => 'ana@turismoapp.test',
            'clave' => 'clave-nueva-123',
            'clave_confirmation' => 'clave-nueva-123',
        ])
        ->call('restablecerClave')
        ->assertSee('No pudimos restablecer tu contraseña. Inténtalo de nuevo en unos minutos.')
        ->assertDontSee('Base de datos no disponible')
        ->assertNoRedirect();

    Exceptions::assertReported(RuntimeException::class);
});
