<?php

use App\Models\Categoria;
use App\Models\Informe;

test('guarda las categorías consultadas del informe en tb_informe_categoria', function () {
    $informe = Informe::factory()->create();
    $categoria = Categoria::factory()->create();

    $informe->categorias()->attach($categoria);

    $this->assertDatabaseHas('tb_informe_categoria', [
        'ica_inf_codigo' => $informe->inf_codigo,
        'ica_cat_codigo' => $categoria->cat_codigo,
    ]);
});
