<?php

namespace App\Services;

use App\Models\Informe;

class InformeExportacionService
{
    /**
     * @return array{
     *     informe: Informe,
     *     estacion: string,
     *     categorias: string,
     *     fecha: string,
     *     zonas: list<array<string, mixed>>,
     *     trenes: list<array<string, mixed>>,
     *     clima: list<array<string, mixed>>
     * }
     */
    public function preparar(Informe $informe): array
    {
        $informe->loadMissing(['estacion', 'categorias']);
        $contenido = is_array($informe->inf_contenido) ? $informe->inf_contenido : [];

        return [
            'informe' => $informe,
            'estacion' => $informe->estacion->est_nombre,
            'categorias' => $informe->categorias->pluck('cat_nombre')->join(', '),
            'fecha' => $informe->inf_fecha_creacion->format('d/m/Y H:i'),
            'zonas' => $this->filas($contenido['zonas'] ?? []),
            'trenes' => $this->filas($contenido['trenes'] ?? []),
            'clima' => $this->filas($contenido['clima'] ?? []),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function filas(mixed $filas): array
    {
        if (! is_array($filas)) {
            return [];
        }

        return array_values(array_filter($filas, is_array(...)));
    }
}
