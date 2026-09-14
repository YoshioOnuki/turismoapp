<?php

use App\Enums\Dificultad;
use App\Enums\FuenteDatos;
use App\Enums\ResultadoSincronizacion;
use App\Enums\TipoPerfil;
use App\Enums\TipoSincronizacion;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use App\Services\ZonaTuristicaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('crea una zona turística con sus imágenes y registra la carga en la bitácora', function () {
    Storage::fake('public');
    $travelGroup = Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create();
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
        'zon_dif_codigo' => Dificultad::Media->value,
    ], [UploadedFile::fake()->image('mirador.jpg')], $travelGroup);

    expect($zona->zon_estado)->toBeTrue()
        ->and($zona->imagenes)->toHaveCount(1);
    Storage::disk('public')->assertExists($zona->imagenes->first()->zim_ruta);
    $this->assertDatabaseHas('tb_zona_turistica', ['zon_nombre' => 'Mirador del Valle']);
    $this->assertDatabaseHas('tb_bitacora', [
        'bit_usu_codigo' => $travelGroup->getKey(),
        'bit_fue_codigo' => FuenteDatos::TravelGroup->value,
        'bit_tsi_codigo' => TipoSincronizacion::Manual->value,
        'bit_registros' => 1,
        'bit_res_codigo' => ResultadoSincronizacion::Exito->value,
        'bit_mensaje' => 'Zona registrada: Mirador del Valle',
    ]);
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
    $this->assertDatabaseHas('tb_bitacora', ['bit_mensaje' => 'Zona actualizada: Nombre actualizado']);
});
