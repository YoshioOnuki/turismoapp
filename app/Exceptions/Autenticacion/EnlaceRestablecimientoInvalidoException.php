<?php

namespace App\Exceptions\Autenticacion;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;

class EnlaceRestablecimientoInvalidoException extends Exception implements ShouldntReport
{
    public function __construct()
    {
        parent::__construct('El enlace de recuperación es inválido o ha expirado.');
    }
}
