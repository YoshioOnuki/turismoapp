<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Parametro;
use App\Models\Usuario;

test('carga los catálogos de categorías y parámetros', function () {
    $this->seed();

    expect(Categoria::pluck('cat_nombre')->all())
        ->toEqualCanonicalizing(['Naturaleza', 'Historia', 'Aventura', 'Gastronomía']);
    expect(Parametro::pluck('par_clave')->all())
        ->toEqualCanonicalizing(['distancia_maxima_caminable', 'frecuencia_sincronizacion', 'velocidad_caminata']);
});

test('crea un usuario de demostración por perfil fuera de producción', function () {
    $this->seed();

    expect(Usuario::pluck('usu_per_codigo')->all())->toEqualCanonicalizing([
        TipoPerfil::UsuarioFinal->value,
        TipoPerfil::TravelGroup->value,
        TipoPerfil::AdministradorMtc->value,
    ]);
});

test('no carga datos de demostración en producción', function () {
    $this->app['env'] = 'production';

    $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

    $this->assertDatabaseCount('tb_categoria', 4);
    $this->assertDatabaseEmpty('tb_usuario');
});
