<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Usuario;
use Livewire\Livewire;

test('el turista consulta, guarda sus preferencias y vuelve al panel', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $naturaleza = Categoria::factory()->create(['cat_nombre' => 'Naturaleza']);
    $historia = Categoria::factory()->create(['cat_nombre' => 'Historia']);

    Livewire::actingAs($turista)
        ->test('pages::turista.preferencias')
        ->assertSeeInOrder(['¿Qué te gustaría descubrir?', 'Historia', 'Naturaleza'])
        ->set('categoriasSeleccionadas', [$naturaleza->getKey(), $historia->getKey()])
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('turista.panel');

    $this->assertDatabaseHas('tb_preferencia', [
        'pre_usu_codigo' => $turista->getKey(),
        'pre_cat_codigo' => $naturaleza->getKey(),
    ]);
    $this->assertDatabaseHas('tb_preferencia', [
        'pre_usu_codigo' => $turista->getKey(),
        'pre_cat_codigo' => $historia->getKey(),
    ]);
});

test('exige al menos una preferencia para continuar', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    Categoria::factory()->create();

    Livewire::actingAs($turista)
        ->test('pages::turista.preferencias')
        ->call('guardar')
        ->assertHasErrors(['categoriasSeleccionadas' => 'required'])
        ->assertSee('Selecciona al menos una categoría para continuar.')
        ->assertNoRedirect();

    $this->assertDatabaseCount('tb_preferencia', 0);
});

test('rechaza categorías inactivas manipuladas desde el cliente', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $categoria = Categoria::factory()->create(['cat_estado' => false]);

    Livewire::actingAs($turista)
        ->test('pages::turista.preferencias')
        ->set('categoriasSeleccionadas', [$categoria->getKey()])
        ->call('guardar')
        ->assertHasErrors(['categoriasSeleccionadas.0' => 'exists']);

    $this->assertDatabaseCount('tb_preferencia', 0);
});

test('un perfil administrativo no puede entrar a las preferencias del turista', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();

    $this->actingAs($administrador)
        ->get(route('turista.preferencias'))
        ->assertForbidden();
});
