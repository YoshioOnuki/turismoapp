<?php

namespace App\Models;

use Database\Factories\ParametroFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Parámetro general que configura el administrador MTC (RF-20).
 */
#[Table(name: 'tb_parametro', key: 'par_codigo')]
#[Fillable(['par_clave', 'par_valor', 'par_descripcion'])]
class Parametro extends Model
{
    /** @use HasFactory<ParametroFactory> */
    use HasFactory;

    public const CREATED_AT = 'par_fecha_creacion';

    public const UPDATED_AT = 'par_fecha_actualizacion';
}
