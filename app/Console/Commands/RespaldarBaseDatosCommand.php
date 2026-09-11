<?php

namespace App\Console\Commands;

use App\Services\RespaldoBaseDatosService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('respaldo:base-datos')]
#[Description('Genera un respaldo privado de la base de datos y conserva las últimas copias')]
class RespaldarBaseDatosCommand extends Command
{
    public function handle(RespaldoBaseDatosService $respaldos): int
    {
        try {
            $ruta = $respaldos->generar();
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->error('No se pudo generar el respaldo de la base de datos. Revisa el registro de la aplicación.');

            return self::FAILURE;
        }

        $this->info("Respaldo creado: {$ruta}");

        return self::SUCCESS;
    }
}
