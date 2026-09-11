<?php

namespace App\Models;

use Database\Factories\InformeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Informe consolidado que generó un usuario (RF-21). Forma su historial (RF-10)
 * y alimenta el reporte de estaciones y categorías más consultadas (RF-24).
 */
#[Table(name: 'tb_informe', key: 'inf_codigo')]
#[Fillable(['inf_est_codigo', 'inf_contenido'])]
class Informe extends Model
{
    /** @use HasFactory<InformeFactory> */
    use HasFactory;

    public const CREATED_AT = 'inf_fecha_creacion';

    /**
     * El informe no se modifica después de generarse.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'inf_contenido' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'inf_usu_codigo', 'usu_codigo');
    }

    /**
     * @return BelongsTo<Estacion, $this>
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'inf_est_codigo', 'est_codigo');
    }

    /**
     * Categorías que el usuario tenía como preferencia al generar el informe.
     *
     * @return BelongsToMany<Categoria, $this>
     */
    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'tb_informe_categoria', 'ica_inf_codigo', 'ica_cat_codigo');
    }
}
