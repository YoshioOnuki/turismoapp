<?php

namespace App\Models;

use Database\Factories\ServicioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo de servicios ferroviarios (Vistadome, Expedition...) que usan los horarios (RF-11).
 */
#[Table(name: 'tb_servicio', key: 'ser_codigo')]
#[Fillable(['ser_nombre'])]
class Servicio extends Model
{
    /** @use HasFactory<ServicioFactory> */
    use HasFactory;

    public const CREATED_AT = 'ser_fecha_creacion';

    public const UPDATED_AT = 'ser_fecha_actualizacion';

    /**
     * @return HasMany<Horario, $this>
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'hor_ser_codigo', 'ser_codigo');
    }
}
