<?php

namespace App\Models;

use Database\Factories\ZonaImagenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'tb_zona_imagen', key: 'zim_codigo')]
#[Fillable(['zim_zon_codigo', 'zim_ruta', 'zim_orden'])]
class ZonaImagen extends Model
{
    /** @use HasFactory<ZonaImagenFactory> */
    use HasFactory;

    public const CREATED_AT = 'zim_fecha_creacion';

    public const UPDATED_AT = 'zim_fecha_actualizacion';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'zim_orden' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ZonaTuristica, $this>
     */
    public function zonaTuristica(): BelongsTo
    {
        return $this->belongsTo(ZonaTuristica::class, 'zim_zon_codigo', 'zon_codigo');
    }
}
