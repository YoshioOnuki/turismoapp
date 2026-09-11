<?php

namespace App\Models;

use Database\Factories\EstacionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Estación ferroviaria sincronizada desde PeruRail (RF-11).
 */
#[Table(name: 'tb_estacion', key: 'est_codigo')]
#[Fillable(['est_codigo_externo', 'est_nombre', 'est_latitud', 'est_longitud', 'est_estado'])]
class Estacion extends Model
{
    /** @use HasFactory<EstacionFactory> */
    use HasFactory;

    public const CREATED_AT = 'est_fecha_creacion';

    public const UPDATED_AT = 'est_fecha_actualizacion';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'est_latitud' => 'float',
            'est_longitud' => 'float',
            'est_estado' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ZonaTuristica, $this>
     */
    public function zonasTuristicas(): HasMany
    {
        return $this->hasMany(ZonaTuristica::class, 'zon_est_codigo', 'est_codigo');
    }

    /**
     * @return HasMany<Clima, $this>
     */
    public function climas(): HasMany
    {
        return $this->hasMany(Clima::class, 'cli_est_codigo', 'est_codigo');
    }

    /**
     * Trenes que llegan a la estación. El informe consolidado los lista con su
     * horario y precio, que son los mismos para todos los usuarios.
     *
     * @return HasMany<Horario, $this>
     */
    public function horariosDeLlegada(): HasMany
    {
        return $this->hasMany(Horario::class, 'hor_est_codigo_destino', 'est_codigo');
    }
}
