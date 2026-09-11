<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;
use Livewire\Livewire;

test('el administrador consulta y crea usuarios', function () {
    $administrador = Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create();

    Livewire::actingAs($administrador)
        ->test('pages::administracion.usuarios')
        ->set('mostrarFormulario', true)
        ->set('nombre', 'Gestor Travel')
        ->set('correo', 'gestor@example.test')
        ->set('clave', 'Clave1234')
        ->set('claveConfirmation', 'Clave1234')
        ->set('perfil', (string) TipoPerfil::TravelGroup->value)
        ->call('crear')
        ->assertHasNoErrors()
        ->assertSee('Usuario creado correctamente.');

    $this->assertDatabaseHas('tb_usuario', [
        'usu_correo' => 'gestor@example.test',
        'usu_per_codigo' => TipoPerfil::TravelGroup->value,
        'usu_estado' => true,
    ]);
});

test('un usuario final no puede gestionar usuarios', function () {
    $usuario = Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create();

    $this->actingAs($usuario)->get(route('administracion.usuarios'))->assertForbidden();
});
