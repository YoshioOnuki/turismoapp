<?php

use App\Enums\TipoPerfil;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use Livewire\Livewire;

test('travel group puede consultar las zonas turísticas', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create();

    $this->actingAs($usuario)
        ->get(route('travel-group.zonas'))
        ->assertOk()
        ->assertSee('Zonas turísticas')
        ->assertSee('Nueva zona');
});

test('un turista no puede administrar zonas turísticas', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();

    $this->actingAs($usuario)
        ->get(route('travel-group.zonas'))
        ->assertForbidden();
});

test('travel group registra y da de baja una zona turística', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create();
    $estacion = Estacion::factory()->create();
    $categoria = Categoria::factory()->create();

    $componente = Livewire::actingAs($usuario)
        ->test('pages::travel-group.zonas')
        ->call('crear')
        ->assertSet('mostrarFormulario', true)
        ->set('nombre', 'Bosque de Nubes')
        ->set('descripcion', 'Sendero interpretativo cercano a la estación.')
        ->set('estacionCodigo', (string) $estacion->getKey())
        ->set('categoriaCodigo', (string) $categoria->getKey())
        ->set('latitud', '-13.1547')
        ->set('longitud', '-72.5252')
        ->set('distancia', '950')
        ->set('dificultad', 'media')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('mostrarFormulario', false)
        ->assertSee('Zona turística creada.');

    $codigo = (int) ZonaTuristica::query()->value('zon_codigo');

    $componente->call('cambiarEstado', $codigo)
        ->assertSee('Zona turística dada de baja.');

    $this->assertDatabaseHas('tb_zona_turistica', [
        'zon_codigo' => $codigo,
        'zon_nombre' => 'Bosque de Nubes',
        'zon_estado' => false,
    ]);
});

test('valida los datos requeridos antes de registrar una zona', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create();

    Livewire::actingAs($usuario)
        ->test('pages::travel-group.zonas')
        ->call('crear')
        ->call('guardar')
        ->assertHasErrors([
            'nombre' => 'required',
            'descripcion' => 'required',
            'estacionCodigo' => 'required',
            'categoriaCodigo' => 'required',
            'latitud' => 'required',
            'longitud' => 'required',
            'distancia' => 'required',
            'dificultad' => 'required',
        ]);
});
