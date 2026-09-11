<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo fijo de perfiles (RF-03). Sus registros los crea la migración de tb_perfil.
 */
#[Table(name: 'tb_perfil', key: 'per_codigo', timestamps: false)]
class Perfil extends Model
{
    /**
     * @return HasMany<Usuario, $this>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'usu_per_codigo', 'per_codigo');
    }
}
