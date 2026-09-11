<?php

namespace App\Models;

use Database\Factories\ClimaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pronóstico del SENAMHI para una estación y una fecha (RF-12). Si el servicio
 * no responde, se muestra el último registro con su fecha de actualización (RNF-09).
 */
#[Table(name: 'tb_clima', key: 'cli_codigo')]
#[Fillable([
    'cli_est_codigo',
    'cli_fecha',
    'cli_temperatura_minima',
    'cli_temperatura_maxima',
    'cli_probabilidad_lluvia',
    'cli_descripcion',
])]
class Clima extends Model
{
    /** @use HasFactory<ClimaFactory> */
    use HasFactory;

    public const CREATED_AT = 'cli_fecha_creacion';

    public const UPDATED_AT = 'cli_fecha_actualizacion';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cli_fecha' => 'date',
            'cli_temperatura_minima' => 'decimal:1',
            'cli_temperatura_maxima' => 'decimal:1',
            'cli_probabilidad_lluvia' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Estacion, $this>
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'cli_est_codigo', 'est_codigo');
    }
}
