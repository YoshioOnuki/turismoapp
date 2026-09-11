<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('autentica al usuario con usu_correo y su clave sin cifrar', function () {
    $usuario = Usuario::factory()->create([
        'usu_correo' => 'turista@turismoapp.test',
        'usu_clave' => 'clave-secreta-123',
    ]);

    $autenticado = Auth::attempt([
        'usu_correo' => 'turista@turismoapp.test',
        'password' => 'clave-secreta-123',
    ]);

    expect($autenticado)->toBeTrue();
    $this->assertAuthenticatedAs($usuario);
});

test('envía el enlace de recuperación de contraseña a usu_correo', function () {
    $usuario = Usuario::factory()->create(['usu_correo' => 'turista@turismoapp.test']);
    Notification::fake();

    $estado = Password::sendResetLink(['usu_correo' => 'turista@turismoapp.test']);

    expect($estado)->toBe(Password::ResetLinkSent);
    $this->assertDatabaseHas('password_reset_tokens', ['email' => 'turista@turismoapp.test']);
    Notification::assertSentTo(
        $usuario,
        ResetPassword::class,
        fn (ResetPassword $notificacion, array $canales, Usuario $notificable) => $notificable->routeNotificationFor('mail', $notificacion) === 'turista@turismoapp.test',
    );
});

test('vincula al usuario con su perfil del catálogo', function (TipoPerfil $tipo, string $nombreDelPerfil) {
    $usuario = Usuario::factory()->conPerfil($tipo)->create();

    expect($usuario->perfil->per_nombre)->toBe($nombreDelPerfil);
    expect($usuario->tienePerfil($tipo))->toBeTrue();
})->with([
    'usuario final' => [TipoPerfil::UsuarioFinal, 'Usuario final'],
    'Travel Group Perú' => [TipoPerfil::TravelGroup, 'Travel Group Perú'],
    'administrador MTC' => [TipoPerfil::AdministradorMtc, 'Administrador MTC'],
]);

test('no reconoce un perfil que el usuario no tiene', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->make();

    expect($usuario->tienePerfil(TipoPerfil::AdministradorMtc))->toBeFalse();
});

test('guarda las preferencias del usuario en tb_preferencia', function () {
    $usuario = Usuario::factory()->create();
    $categoria = Categoria::factory()->create();

    $usuario->preferencias()->attach($categoria);

    $this->assertDatabaseHas('tb_preferencia', [
        'pre_usu_codigo' => $usuario->usu_codigo,
        'pre_cat_codigo' => $categoria->cat_codigo,
    ]);
});
