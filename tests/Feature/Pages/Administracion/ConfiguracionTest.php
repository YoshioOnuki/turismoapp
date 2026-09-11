<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Parametro;
use App\Models\Usuario;
use Livewire\Livewire;

test('el administrador consulta parámetros y categorías en tablas', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    Parametro::factory()->create([
        'par_clave' => 'velocidad_caminata',
        'par_descripcion' => 'Velocidad promedio de caminata',
    ]);
    Categoria::factory()->create(['cat_nombre' => 'Naturaleza']);

    $this->actingAs($administrador)
        ->get(route('administracion.configuracion'))
        ->assertOk()
        ->assertSeeInOrder(['Parámetros generales', 'Velocidad promedio de caminata', 'Categorías turísticas', 'Naturaleza']);
});

test('el administrador actualiza un parámetro desde su modal', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    $parametro = Parametro::factory()->create([
        'par_clave' => 'frecuencia_sincronizacion',
        'par_valor' => '24',
    ]);

    Livewire::actingAs($administrador)
        ->test('pages::administracion.configuracion')
        ->call('editarParametro', $parametro->getKey())
        ->assertSet('mostrarParametro', true)
        ->set('parametroValor', '8')
        ->call('guardarParametro')
        ->assertHasNoErrors()
        ->assertSet('mostrarParametro', false)
        ->assertSee('Parámetro actualizado correctamente.');

    $this->assertDatabaseHas('tb_parametro', [
        'par_codigo' => $parametro->getKey(),
        'par_valor' => '8',
    ]);
});

test('el administrador crea y da de baja una categoría', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();

    $componente = Livewire::actingAs($administrador)
        ->test('pages::administracion.configuracion')
        ->call('nuevaCategoria')
        ->assertSet('mostrarCategoria', true)
        ->set('categoriaNombre', 'Artesanía')
        ->call('guardarCategoria')
        ->assertHasNoErrors()
        ->assertSet('mostrarCategoria', false);
    $categoria = Categoria::query()->where('cat_nombre', 'Artesanía')->firstOrFail();

    $componente->call('cambiarEstadoCategoria', $categoria->getKey())
        ->assertSee('Categoría dada de baja.');

    expect($categoria->fresh()->cat_estado)->toBeFalse();
});

test('valida los límites de los parámetros conocidos', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();
    $parametro = Parametro::factory()->create(['par_clave' => 'frecuencia_sincronizacion']);

    Livewire::actingAs($administrador)
        ->test('pages::administracion.configuracion')
        ->call('editarParametro', $parametro->getKey())
        ->set('parametroValor', '0')
        ->call('guardarParametro')
        ->assertHasErrors(['parametroValor' => 'min']);
});

test('un usuario sin perfil administrador no puede gestionar la configuración', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create();

    $this->actingAs($usuario)
        ->get(route('administracion.configuracion'))
        ->assertForbidden();
});
