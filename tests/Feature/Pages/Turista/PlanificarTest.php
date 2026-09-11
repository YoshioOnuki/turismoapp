<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use Livewire\Livewire;

test('el turista selecciona una estación y consulta sus zonas disponibles', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create(['est_nombre' => 'Estación Central']);
    $categoria = Categoria::factory()->create();
    $turista->preferencias()->attach($categoria);
    ZonaTuristica::factory()->create([
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $categoria->getKey(),
        'zon_nombre' => 'Mirador del Valle',
        'zon_distancia' => 500,
    ]);

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->assertSee('Estación Central')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertHasNoErrors()
        ->assertSee('Mirador del Valle')
        ->assertSee('Trenes que llegan a la estación')
        ->assertSee('Pronóstico del clima');
});

test('avisa cuando no hay zonas que coincidan', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create();

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertSee('No encontramos zonas que coincidan');
});

test('rechaza una estación inactiva manipulada desde el cliente', function () {
    $turista = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();
    $estacion = Estacion::factory()->create(['est_estado' => false]);

    Livewire::actingAs($turista)
        ->test('pages::turista.planificar')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->call('buscar')
        ->assertHasErrors(['estacionCodigo' => 'exists']);
});
