<?php

namespace App\Enums;

/**
 * Resultado de una sincronización registrada en tb_bitacora (RF-14).
 * Cada valor coincide con res_codigo en tb_resultado_sincronizacion.
 */
enum ResultadoSincronizacion: int
{
    case Exito = 1;
    case Error = 2;

    /**
     * Nombre visible del resultado; es el mismo que res_nombre en tb_resultado_sincronizacion.
     */
    public function etiqueta(): string
    {
        return match ($this) {
            self::Exito => 'Éxito',
            self::Error => 'Error',
        };
    }
}
