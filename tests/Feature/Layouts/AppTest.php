<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;

test('ofrece iniciar sesión y crear cuenta a los invitados', function () {
    $this->get(route('inicio'))
        ->assertSee(route('login'))
        ->assertSee(route('registro'));
});

test('muestra el nombre, el perfil y el acceso al panel del usuario', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create(['usu_nombre' => 'Ana Quispe']);

    $this->actingAs($usuario)
        ->get(route('inicio'))
        ->assertSee('Ana Quispe')
        ->assertSee('Administrador MTC')
        ->assertSee(route('administracion.panel'))
        ->assertSee(route('logout'))
        ->assertDontSee(route('login'));
});

test('escapa el nombre del usuario en la barra de navegación', function () {
    $this->actingAs(Usuario::factory()->create(['usu_nombre' => '<script>alert("xss")</script>']))
        ->get(route('inicio'))
        ->assertSee('&lt;script&gt;', false)
        ->assertDontSee('<script>alert("xss")</script>', false);
});
