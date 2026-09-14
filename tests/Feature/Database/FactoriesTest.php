<?php

use App\Models\Bitacora;
use App\Models\Categoria;
use App\Models\Clima;
use App\Models\Estacion;
use App\Models\Horario;
use App\Models\Informe;
use App\Models\Parametro;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Models\ZonaImagen;
use App\Models\ZonaTuristica;

test('crea un registro válido con la factory de cada modelo', function (string $modelo) {
    $registro = $modelo::factory()->create();

    $this->assertModelExists($registro);
})->with([
    'Usuario' => Usuario::class,
    'Categoria' => Categoria::class,
    'Estacion' => Estacion::class,
    'ZonaTuristica' => ZonaTuristica::class,
    'ZonaImagen' => ZonaImagen::class,
    'Clima' => Clima::class,
    'Servicio' => Servicio::class,
    'Horario' => Horario::class,
    'Informe' => Informe::class,
    'Parametro' => Parametro::class,
    'Bitacora' => Bitacora::class,
]);
