<?php

test('redirige la raíz a la página de inicio', function () {
    $response = $this->get('/');

    $response->assertRedirectToRoute('inicio');
});
