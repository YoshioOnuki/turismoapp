<?php

test('marca como próximo un módulo que todavía no tiene enlace', function () {
    $vista = $this->blade('<x-tarjeta-modulo icono="map" titulo="Planificar mi visita" descripcion="Elige una estación." />');

    $vista->assertSee('Planificar mi visita')
        ->assertSee('Próximamente')
        ->assertDontSee('Abrir módulo');
});

test('enlaza el módulo cuando ya tiene ruta', function () {
    $vista = $this->blade('<x-tarjeta-modulo icono="map" titulo="Planificar mi visita" descripcion="Elige una estación." href="/turista/planificar" />');

    $vista->assertSee('href="/turista/planificar"', false)
        ->assertSee('Abrir módulo')
        ->assertDontSee('Próximamente');
});
