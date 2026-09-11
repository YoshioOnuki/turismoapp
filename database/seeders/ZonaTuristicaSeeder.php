<?php

namespace Database\Seeders;

use App\Enums\Dificultad;
use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\ZonaTuristica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ZonaTuristicaSeeder extends Seeder
{
    /**
     * Zonas turísticas de demostración con coordenadas y distancias aproximadas.
     * Poroy queda sin zonas para mostrar el caso sin coincidencias (RF-09).
     */
    public function run(): void
    {
        $estaciones = Estacion::pluck('est_codigo', 'est_codigo_externo');
        $categorias = Categoria::pluck('cat_codigo', 'cat_nombre');

        foreach ($this->zonas() as $zona) {
            ZonaTuristica::updateOrCreate(
                ['zon_nombre' => $zona['zon_nombre']],
                [
                    ...Arr::except($zona, ['estacion', 'categoria']),
                    'zon_est_codigo' => $estaciones[$zona['estacion']],
                    'zon_cat_codigo' => $categorias[$zona['categoria']],
                ],
            );
        }
    }

    /**
     * @return list<array{estacion: string, categoria: string, zon_nombre: string, zon_descripcion: string, zon_latitud: float, zon_longitud: float, zon_distancia: int, zon_dificultad: Dificultad}>
     */
    private function zonas(): array
    {
        return [
            [
                'estacion' => 'MAP',
                'categoria' => 'Gastronomía',
                'zon_nombre' => 'Mercado de Aguas Calientes',
                'zon_descripcion' => 'Mercado con platos típicos, frutas y artesanía junto a la estación.',
                'zon_latitud' => -13.1544,
                'zon_longitud' => -72.5247,
                'zon_distancia' => 150,
                'zon_dificultad' => Dificultad::Baja,
            ],
            [
                'estacion' => 'MAP',
                'categoria' => 'Naturaleza',
                'zon_nombre' => 'Aguas Termales de Aguas Calientes',
                'zon_descripcion' => 'Pozas de aguas termales al final de la avenida Pachacútec, en subida.',
                'zon_latitud' => -13.1575,
                'zon_longitud' => -72.5195,
                'zon_distancia' => 850,
                'zon_dificultad' => Dificultad::Media,
            ],
            [
                'estacion' => 'MAP',
                'categoria' => 'Naturaleza',
                'zon_nombre' => 'Mariposario de Machu Picchu',
                'zon_descripcion' => 'Refugio de mariposas nativas en el camino hacia Puente Ruinas.',
                'zon_latitud' => -13.1601,
                'zon_longitud' => -72.5327,
                'zon_distancia' => 1300,
                'zon_dificultad' => Dificultad::Baja,
            ],
            [
                'estacion' => 'MAP',
                'categoria' => 'Historia',
                'zon_nombre' => 'Museo de Sitio Manuel Chávez Ballón',
                'zon_descripcion' => 'Museo sobre la historia de Machu Picchu, junto al Jardín Botánico, al pie de la montaña.',
                'zon_latitud' => -13.1668,
                'zon_longitud' => -72.5381,
                'zon_distancia' => 1900,
                'zon_dificultad' => Dificultad::Media,
            ],
            [
                'estacion' => 'MAP',
                'categoria' => 'Aventura',
                'zon_nombre' => 'Montaña Putucusi',
                'zon_descripcion' => 'Ascenso por escalinatas empinadas con vista frente a Machu Picchu.',
                'zon_latitud' => -13.1531,
                'zon_longitud' => -72.5222,
                'zon_distancia' => 700,
                'zon_dificultad' => Dificultad::Alta,
            ],
            [
                'estacion' => 'OLL',
                'categoria' => 'Historia',
                'zon_nombre' => 'Parque Arqueológico de Ollantaytambo',
                'zon_descripcion' => 'Terrazas y templo inca en la ladera, a pocos minutos de la plaza del pueblo.',
                'zon_latitud' => -13.2573,
                'zon_longitud' => -72.2665,
                'zon_distancia' => 900,
                'zon_dificultad' => Dificultad::Media,
            ],
            [
                'estacion' => 'OLL',
                'categoria' => 'Aventura',
                'zon_nombre' => 'Pinkuylluna',
                'zon_descripcion' => 'Caminata empinada hasta antiguos depósitos incas con vista al pueblo y al valle.',
                'zon_latitud' => -13.2566,
                'zon_longitud' => -72.2621,
                'zon_distancia' => 1000,
                'zon_dificultad' => Dificultad::Alta,
            ],
            [
                'estacion' => 'OLL',
                'categoria' => 'Gastronomía',
                'zon_nombre' => 'Mercado de Ollantaytambo',
                'zon_descripcion' => 'Puestos de comida y bebidas tradicionales frente a la plaza principal.',
                'zon_latitud' => -13.2583,
                'zon_longitud' => -72.2636,
                'zon_distancia' => 600,
                'zon_dificultad' => Dificultad::Baja,
            ],
            [
                'estacion' => 'WAN',
                'categoria' => 'Historia',
                'zon_nombre' => 'Qoricancha',
                'zon_descripcion' => 'Templo inca del Sol sobre el que se levantó el convento de Santo Domingo.',
                'zon_latitud' => -13.5198,
                'zon_longitud' => -71.9750,
                'zon_distancia' => 1200,
                'zon_dificultad' => Dificultad::Baja,
            ],
            [
                'estacion' => 'WAN',
                'categoria' => 'Historia',
                'zon_nombre' => 'Plaza de Armas del Cusco',
                'zon_descripcion' => 'Centro histórico con la Catedral y la iglesia de la Compañía de Jesús.',
                'zon_latitud' => -13.5163,
                'zon_longitud' => -71.9785,
                'zon_distancia' => 1800,
                'zon_dificultad' => Dificultad::Baja,
            ],
            [
                'estacion' => 'WAN',
                'categoria' => 'Gastronomía',
                'zon_nombre' => 'Mercado Central de San Pedro',
                'zon_descripcion' => 'Mercado tradicional con jugos, panes y platos cusqueños.',
                'zon_latitud' => -13.5220,
                'zon_longitud' => -71.9843,
                'zon_distancia' => 2200,
                'zon_dificultad' => Dificultad::Baja,
            ],
            [
                'estacion' => 'WAN',
                'categoria' => 'Historia',
                'zon_nombre' => 'Parque Arqueológico de Sacsayhuamán',
                'zon_descripcion' => 'Fortaleza inca de grandes muros de piedra; se llega por una subida exigente.',
                'zon_latitud' => -13.5093,
                'zon_longitud' => -71.9819,
                'zon_distancia' => 3400,
                'zon_dificultad' => Dificultad::Alta,
            ],
            [
                'estacion' => 'PUN',
                'categoria' => 'Historia',
                'zon_nombre' => 'Catedral de Puno',
                'zon_descripcion' => 'Catedral barroca de piedra en la Plaza de Armas.',
                'zon_latitud' => -15.8406,
                'zon_longitud' => -70.0280,
                'zon_distancia' => 600,
                'zon_dificultad' => Dificultad::Baja,
            ],
            [
                'estacion' => 'PUN',
                'categoria' => 'Naturaleza',
                'zon_nombre' => 'Muelle del Lago Titicaca',
                'zon_descripcion' => 'Orilla del lago y punto de partida de los paseos en bote hacia los Uros.',
                'zon_latitud' => -15.8328,
                'zon_longitud' => -70.0148,
                'zon_distancia' => 1400,
                'zon_dificultad' => Dificultad::Baja,
            ],
            [
                'estacion' => 'PUN',
                'categoria' => 'Aventura',
                'zon_nombre' => 'Mirador Kuntur Wasi',
                'zon_descripcion' => 'Subida por escaleras hasta el mirador del cóndor con vista a la bahía.',
                'zon_latitud' => -15.8378,
                'zon_longitud' => -70.0360,
                'zon_distancia' => 1700,
                'zon_dificultad' => Dificultad::Alta,
            ],
            [
                'estacion' => 'PUN',
                'categoria' => 'Gastronomía',
                'zon_nombre' => 'Mercado Central de Puno',
                'zon_descripcion' => 'Mercado con quinua, trucha y comida típica del altiplano.',
                'zon_latitud' => -15.8424,
                'zon_longitud' => -70.0262,
                'zon_distancia' => 500,
                'zon_dificultad' => Dificultad::Baja,
            ],
        ];
    }
}
