<?php

namespace App\Models;

use App\Enums\Dificultad;
use Database\Factories\ZonaTuristicaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Zona turística que Travel Group Perú registra y mantiene (RF-16, RF-17).
 */
#[Table(name: 'tb_zona_turistica', key: 'zon_codigo')]
#[Fillable([
    'zon_est_codigo',
    'zon_cat_codigo',
    'zon_nombre',
    'zon_descripcion',
    'zon_latitud',
    'zon_longitud',
    'zon_distancia',
    'zon_dif_codigo',
    'zon_estado',
])]
class ZonaTuristica extends Model
{
    /** @use HasFactory<ZonaTuristicaFactory> */
    use HasFactory;

    public const CREATED_AT = 'zon_fecha_creacion';

    public const UPDATED_AT = 'zon_fecha_actualizacion';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'zon_latitud' => 'float',
            'zon_longitud' => 'float',
            'zon_distancia' => 'integer',
            'zon_dif_codigo' => Dificultad::class,
            'zon_estado' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Estacion, $this>
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'zon_est_codigo', 'est_codigo');
    }

    /**
     * @return BelongsTo<Categoria, $this>
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'zon_cat_codigo', 'cat_codigo');
    }

    /**
     * @return HasMany<ZonaImagen, $this>
     */
    public function imagenes(): HasMany
    {
        return $this->hasMany(ZonaImagen::class, 'zim_zon_codigo', 'zon_codigo');
    }
}
