<?php

test('marca como próximo un módulo que todavía no tiene enlace', function () {
    $vista = $this->blade('<x-tarjeta-modulo icono="map" titulo="Planificar mi visita" descripcion="Elige una estación." />');

    $vista->assertSee('Planificar mi visita')
        ->assertSee('Próximamente')
        ->assertDontSee('Entrar');
});

test('enlaza el módulo cuando ya tiene ruta', function () {
    $vista = $this->blade('<x-tarjeta-modulo icono="map" titulo="Planificar mi visita" descripcion="Elige una estación." href="/turista/planificar" />');

    $vista->assertSee('href="/turista/planificar"', false)
        ->assertSee('Entrar')
        ->assertDontSee('Próximamente');
});
