<?php

use App\Enums\Dificultad;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\ZonaTuristica;
use App\Services\ZonaTuristicaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('crea una zona turística con sus imágenes', function () {
    Storage::fake('public');
    $estacion = Estacion::factory()->create();
    $categoria = Categoria::factory()->create();

    $zona = app(ZonaTuristicaService::class)->guardar(null, [
        'zon_est_codigo' => $estacion->getKey(),
        'zon_cat_codigo' => $categoria->getKey(),
        'zon_nombre' => 'Mirador del Valle',
        'zon_descripcion' => 'Vista panorámica del valle.',
        'zon_latitud' => -13.5,
        'zon_longitud' => -72.1,
        'zon_distancia' => 850,
        'zon_dificultad' => Dificultad::Media->value,
    ], [UploadedFile::fake()->image('mirador.jpg')]);

    expect($zona->zon_estado)->toBeTrue()
        ->and($zona->imagenes)->toHaveCount(1);
    Storage::disk('public')->assertExists($zona->imagenes->first()->zim_ruta);
    $this->assertDatabaseHas('tb_zona_turistica', ['zon_nombre' => 'Mirador del Valle']);
});

test('actualiza una zona y permite darla de baja sin eliminarla', function () {
    $zona = ZonaTuristica::factory()->create();
    $servicio = app(ZonaTuristicaService::class);

    $servicio->guardar($zona->getKey(), [
        'zon_nombre' => 'Nombre actualizado',
    ], []);
    $servicio->cambiarEstado($zona->getKey());

    expect($zona->fresh()->zon_nombre)->toBe('Nombre actualizado')
        ->and($zona->fresh()->zon_estado)->toBeFalse();
    $this->assertDatabaseCount('tb_zona_turistica', 1);
});
