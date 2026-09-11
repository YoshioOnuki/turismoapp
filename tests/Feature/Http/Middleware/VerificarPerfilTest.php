<?php

use App\Enums\TipoPerfil;
use App\Http\Middleware\VerificarPerfil;
use App\Models\Usuario;
use Illuminate\Support\Facades\Route;

test('deja entrar a cada perfil a su panel', function (TipoPerfil $perfil, string $panel) {
    $usuario = Usuario::factory()->conPerfil($perfil)->create();

    $this->actingAs($usuario)->get(route($panel))->assertOk();
})->with([
    'usuario final' => [TipoPerfil::UsuarioFinal, 'turista.panel'],
    'Travel Group Perú' => [TipoPerfil::TravelGroup, 'travel-group.panel'],
    'administrador MTC' => [TipoPerfil::AdministradorMtc, 'administracion.panel'],
]);

test('niega el acceso a los paneles de otros perfiles', function (TipoPerfil $perfil, string $panel) {
    $usuario = Usuario::factory()->conPerfil($perfil)->create();

    $this->actingAs($usuario)->get(route($panel))->assertForbidden();
})->with([
    'usuario final en Travel Group' => [TipoPerfil::UsuarioFinal, 'travel-group.panel'],
    'usuario final en administración' => [TipoPerfil::UsuarioFinal, 'administracion.panel'],
    'Travel Group en turista' => [TipoPerfil::TravelGroup, 'turista.panel'],
    'Travel Group en administración' => [TipoPerfil::TravelGroup, 'administracion.panel'],
    'administrador en turista' => [TipoPerfil::AdministradorMtc, 'turista.panel'],
    'administrador en Travel Group' => [TipoPerfil::AdministradorMtc, 'travel-group.panel'],
]);

test('pide iniciar sesión a los invitados', function () {
    $this->get(route('administracion.panel'))->assertRedirectToRoute('login');
});

test('avisa en español que el acceso está denegado', function () {
    $this->actingAs(Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create())
        ->get(route('administracion.panel'))
        ->assertForbidden()
        ->assertSee('Acceso denegado');
});

test('admite varios perfiles en una misma ruta', function (TipoPerfil $perfil) {
    Route::middleware(VerificarPerfil::permitir(TipoPerfil::TravelGroup, TipoPerfil::AdministradorMtc))
        ->get('/prueba/varios-perfiles', fn () => 'ok');

    $this->actingAs(Usuario::factory()->conPerfil($perfil)->create())
        ->get('/prueba/varios-perfiles')
        ->assertOk();
})->with([
    'Travel Group Perú' => TipoPerfil::TravelGroup,
    'administrador MTC' => TipoPerfil::AdministradorMtc,
]);
