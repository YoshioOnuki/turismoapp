<?php

use App\Models\Usuario;
use App\Services\AutenticacionService;
use Illuminate\Support\Facades\Exceptions;
use Livewire\Livewire;

test('muestra la página de registro a los invitados', function () {
    $this->get(route('registro'))->assertSeeLivewire('pages::auth.registro');
});

test('registra al turista, le abre la sesión y lo lleva a elegir sus preferencias', function () {
    Livewire::test('pages::auth.registro')
        ->set([
            'nombre' => 'Ana Quispe',
            'correo' => 'ana@turismoapp.test',
            'clave' => 'clave-segura-123',
            'clave_confirmation' => 'clave-segura-123',
        ])
        ->call('registrar')
        ->assertRedirectToRoute('turista.preferencias');

    $this->assertAuthenticatedAs(Usuario::where('usu_correo', 'ana@turismoapp.test')->sole());
});

test('guarda el correo en minúsculas y sin espacios alrededor', function () {
    Livewire::test('pages::auth.registro')
        ->set([
            'nombre' => 'Ana Quispe',
            'correo' => '  Ana@TurismoApp.TEST ',
            'clave' => 'clave-segura-123',
            'clave_confirmation' => 'clave-segura-123',
        ])
        ->call('registrar');

    $this->assertDatabaseHas('tb_usuario', ['usu_correo' => 'ana@turismoapp.test']);
});

test('exige nombre, correo y contraseña', function () {
    Livewire::test('pages::auth.registro')
        ->call('registrar')
        ->assertHasErrors(['nombre' => 'required', 'correo' => 'required', 'clave' => 'required'])
        ->assertSee('El campo nombre es obligatorio.')
        ->assertSee('El campo correo electrónico es obligatorio.')
        ->assertSee('El campo contraseña es obligatorio.');
});

test('rechaza un correo que ya está registrado aunque cambien las mayúsculas', function () {
    Usuario::factory()->create(['usu_correo' => 'ana@turismoapp.test']);

    Livewire::test('pages::auth.registro')
        ->set([
            'nombre' => 'Ana Quispe',
            'correo' => 'ANA@turismoapp.test',
            'clave' => 'clave-segura-123',
            'clave_confirmation' => 'clave-segura-123',
        ])
        ->call('registrar')
        ->assertHasErrors(['correo' => 'unique'])
        ->assertSee('El valor del campo correo electrónico ya está en uso.');

    $this->assertDatabaseCount('tb_usuario', 1);
});

test('exige que la confirmación coincida con la contraseña', function () {
    Livewire::test('pages::auth.registro')
        ->set([
            'nombre' => 'Ana Quispe',
            'correo' => 'ana@turismoapp.test',
            'clave' => 'clave-segura-123',
            'clave_confirmation' => 'otra-clave-123',
        ])
        ->call('registrar')
        ->assertHasErrors(['clave' => 'confirmed'])
        ->assertSee('El campo confirmación de contraseña no coincide.');

    $this->assertDatabaseEmpty('tb_usuario');
});

test('exige una contraseña de al menos ocho caracteres', function () {
    Livewire::test('pages::auth.registro')
        ->set([
            'nombre' => 'Ana Quispe',
            'correo' => 'ana@turismoapp.test',
            'clave' => 'corta12',
            'clave_confirmation' => 'corta12',
        ])
        ->call('registrar')
        ->assertHasErrors('clave')
        ->assertSee('El campo contraseña debe contener al menos 8 caracteres.');

    $this->assertDatabaseEmpty('tb_usuario');
});

test('muestra un mensaje general y registra el error si el servicio falla', function () {
    Exceptions::fake();
    $this->mock(AutenticacionService::class)
        ->shouldReceive('registrar')
        ->andThrow(new RuntimeException('Base de datos no disponible'));

    Livewire::test('pages::auth.registro')
        ->set([
            'nombre' => 'Ana Quispe',
            'correo' => 'ana@turismoapp.test',
            'clave' => 'clave-segura-123',
            'clave_confirmation' => 'clave-segura-123',
        ])
        ->call('registrar')
        ->assertSee('No pudimos crear tu cuenta. Inténtalo de nuevo en unos minutos.')
        ->assertDontSee('Base de datos no disponible')
        ->assertNoRedirect();

    Exceptions::assertReported(RuntimeException::class);
});
