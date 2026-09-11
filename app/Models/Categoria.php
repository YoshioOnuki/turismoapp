<?php

namespace App\Models;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo de categorías turísticas que administra el MTC (RF-20).
 */
#[Table(name: 'tb_categoria', key: 'cat_codigo')]
#[Fillable(['cat_nombre', 'cat_estado'])]
class Categoria extends Model
{
    /** @use HasFactory<CategoriaFactory> */
    use HasFactory;

    public const CREATED_AT = 'cat_fecha_creacion';

    public const UPDATED_AT = 'cat_fecha_actualizacion';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cat_estado' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ZonaTuristica, $this>
     */
    public function zonasTuristicas(): HasMany
    {
        return $this->hasMany(ZonaTuristica::class, 'zon_cat_codigo', 'cat_codigo');
    }
}
