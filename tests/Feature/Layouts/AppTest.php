<?php

use App\Models\Usuario;

test('ofrece iniciar sesión y crear cuenta a los invitados', function () {
    $this->get(route('inicio'))
        ->assertSee(route('login'))
        ->assertSee(route('registro'));
});

test('muestra el nombre del usuario y la opción de cerrar sesión', function () {
    $this->actingAs(Usuario::factory()->create(['usu_nombre' => 'Ana Quispe']))
        ->get(route('inicio'))
        ->assertSee('Ana Quispe')
        ->assertSee(route('logout'))
        ->assertDontSee(route('login'));
});

test('escapa el nombre del usuario en la barra de navegación', function () {
    $this->actingAs(Usuario::factory()->create(['usu_nombre' => '<script>alert("xss")</script>']))
        ->get(route('inicio'))
        ->assertSee('&lt;script&gt;', false)
        ->assertDontSee('<script>alert("xss")</script>', false);
});
