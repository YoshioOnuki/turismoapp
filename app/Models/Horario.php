<?php

namespace App\Models;

use Database\Factories\HorarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Tren de PeruRail entre una estación de origen y una de destino (RF-11).
 */
#[Table(name: 'tb_horario', key: 'hor_codigo')]
#[Fillable([
    'hor_codigo_externo',
    'hor_est_codigo_origen',
    'hor_est_codigo_destino',
    'hor_ser_codigo',
    'hor_hora_salida',
    'hor_hora_llegada',
    'hor_precio',
    'hor_estado',
])]
class Horario extends Model
{
    /** @use HasFactory<HorarioFactory> */
    use HasFactory;

    public const CREATED_AT = 'hor_fecha_creacion';

    public const UPDATED_AT = 'hor_fecha_actualizacion';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hor_precio' => 'decimal:2',
            'hor_estado' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Estacion, $this>
     */
    public function estacionOrigen(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'hor_est_codigo_origen', 'est_codigo');
    }

    /**
     * @return BelongsTo<Estacion, $this>
     */
    public function estacionDestino(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'hor_est_codigo_destino', 'est_codigo');
    }

    /**
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'hor_ser_codigo', 'ser_codigo');
    }

    /**
     * Tiempo de viaje del tren, calculado con la hora de salida y la de llegada.
     */
    public function duracionEnMinutos(): int
    {
        $salida = Carbon::parse($this->hor_hora_salida);
        $llegada = Carbon::parse($this->hor_hora_llegada);

        if ($llegada->lessThan($salida)) {
            $llegada->addDay();
        }

        return (int) $salida->diffInMinutes($llegada);
    }

    /**
     * Tiempo de viaje en texto, por ejemplo "1 h 30 min" (RF-11).
     */
    public function duracionFormateada(): string
    {
        $minutos = $this->duracionEnMinutos();
        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        return match (true) {
            $horas === 0 => "{$resto} min",
            $resto === 0 => "{$horas} h",
            default => "{$horas} h {$resto} min",
        };
    }
}
