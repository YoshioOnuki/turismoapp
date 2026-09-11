<?php

namespace App\Exceptions\Autenticacion;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

/**
 * Se superó el límite de intentos de inicio de sesión y hay que esperar para reintentar.
 */
class DemasiadosIntentosException extends RuntimeException implements ShouldntReport
{
    public function __construct(public readonly int $segundos)
    {
        parent::__construct("Demasiados intentos de inicio de sesión. Reintentar en {$segundos} segundos.");
    }
}
