<?php

namespace App\Models;

use App\Enums\FuenteDatos;
use App\Enums\ResultadoSincronizacion;
use App\Enums\TipoSincronizacion;
use Database\Factories\BitacoraFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de cada sincronización con una fuente externa (RF-14, RF-15).
 */
#[Table(name: 'tb_bitacora', key: 'bit_codigo', timestamps: false)]
#[Fillable([
    'bit_usu_codigo',
    'bit_fuente',
    'bit_tipo',
    'bit_fecha_inicio',
    'bit_fecha_fin',
    'bit_registros',
    'bit_resultado',
    'bit_mensaje',
])]
class Bitacora extends Model
{
    /** @use HasFactory<BitacoraFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bit_fuente' => FuenteDatos::class,
            'bit_tipo' => TipoSincronizacion::class,
            'bit_fecha_inicio' => 'datetime',
            'bit_fecha_fin' => 'datetime',
            'bit_registros' => 'integer',
            'bit_resultado' => ResultadoSincronizacion::class,
        ];
    }

    /**
     * Administrador que lanzó la sincronización manual; es nulo en las automáticas.
     *
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'bit_usu_codigo', 'usu_codigo');
    }
}
