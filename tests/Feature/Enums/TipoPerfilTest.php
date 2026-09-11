<?php

use App\Enums\TipoPerfil;
use App\Models\Perfil;

test('muestra el mismo nombre de perfil que guarda tb_perfil', function (TipoPerfil $perfil) {
    expect($perfil->etiqueta())->toBe(Perfil::findOrFail($perfil->value)->per_nombre);
})->with(TipoPerfil::cases());
