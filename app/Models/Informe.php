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
#[Fillable(['inf_est_codigo'])]
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

    /**
     * Zonas recomendadas con el recorrido y el tiempo calculados al generar el informe.
     *
     * @return BelongsToMany<ZonaTuristica, $this>
     */
    public function zonas(): BelongsToMany
    {
        return $this->belongsToMany(ZonaTuristica::class, 'tb_informe_zona', 'izo_inf_codigo', 'izo_zon_codigo')
            ->withPivot(['izo_distancia_total', 'izo_tiempo_minutos']);
    }

    /**
     * Trenes de llegada con el precio del boleto vigente al generar el informe.
     *
     * @return BelongsToMany<Horario, $this>
     */
    public function horarios(): BelongsToMany
    {
        return $this->belongsToMany(Horario::class, 'tb_informe_horario', 'iho_inf_codigo', 'iho_hor_codigo')
            ->withPivot('iho_precio');
    }

    /**
     * Pronósticos del clima incluidos en el informe.
     *
     * @return BelongsToMany<Clima, $this>
     */
    public function climas(): BelongsToMany
    {
        return $this->belongsToMany(Clima::class, 'tb_informe_clima', 'icl_inf_codigo', 'icl_cli_codigo');
    }
}
