<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;

test('ofrece iniciar sesión y crear cuenta a los invitados', function () {
    $this->get(route('inicio'))
        ->assertSee(route('login'))
        ->assertSee(route('registro'));
});

test('muestra el nombre, el perfil y la navegación correspondiente al administrador', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create(['usu_nombre' => 'Ana Quispe']);

    $this->actingAs($usuario)
        ->get(route('inicio'))
        ->assertSee('Ana Quispe')
        ->assertSee('Administrador MTC')
        ->assertSee(route('administracion.panel'))
        ->assertSee(route('administracion.usuarios'))
        ->assertSee(route('administracion.horarios'))
        ->assertSee(route('administracion.sincronizacion'))
        ->assertSee(route('administracion.configuracion'))
        ->assertSee(route('administracion.reporte-uso'))
        ->assertSee(route('logout'))
        ->assertDontSee(route('turista.planificar'))
        ->assertDontSee(route('login'));
});

test('escapa el nombre del usuario en la barra de navegación', function () {
    $this->actingAs(Usuario::factory()->create(['usu_nombre' => '<script>alert("xss")</script>']))
        ->get(route('inicio'))
        ->assertSee('&lt;script&gt;', false)
        ->assertDontSee('<script>alert("xss")</script>', false);
});
