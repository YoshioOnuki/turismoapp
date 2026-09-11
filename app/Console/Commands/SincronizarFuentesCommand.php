<?php

namespace App\Console\Commands;

use App\Enums\ResultadoSincronizacion;
use App\Services\SincronizacionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sincronizacion:ejecutar')]
#[Description('Actualiza las fuentes que superaron la frecuencia configurada')]
class SincronizarFuentesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SincronizacionService $sincronizacion): int
    {
        $resultados = $sincronizacion->ejecutarAutomaticamenteSiCorresponde();

        if ($resultados === []) {
            $this->info('No hay fuentes pendientes de sincronización.');

            return self::SUCCESS;
        }

        $huboErrores = false;

        foreach ($resultados as $fuente => $bitacora) {
            if ($bitacora->bit_resultado === ResultadoSincronizacion::Error) {
                $huboErrores = true;
                $this->error("{$fuente}: no pudo sincronizarse.");

                continue;
            }

            $this->info("{$fuente}: {$bitacora->bit_registros} registros sincronizados.");
        }

        return $huboErrores ? self::FAILURE : self::SUCCESS;
    }
}
