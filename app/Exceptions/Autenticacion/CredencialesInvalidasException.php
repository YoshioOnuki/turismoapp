<?php

namespace App\Exceptions\Autenticacion;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

/**
 * El correo y la contraseña no corresponden a un usuario activo.
 */
class CredencialesInvalidasException extends RuntimeException implements ShouldntReport
{
    //
}
