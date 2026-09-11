<?php

use App\Models\Usuario;

test('cierra la sesión y lleva a la página de inicio', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('logout'))->assertRedirectToRoute('inicio');

    $this->assertGuest();
});

test('pide iniciar sesión a un invitado que intenta cerrar sesión', function () {
    $this->post(route('logout'))->assertRedirectToRoute('login');
});
