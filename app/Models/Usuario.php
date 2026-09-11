<?php

namespace App\Models;

use App\Enums\TipoPerfil;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

#[Table(name: 'tb_usuario', key: 'usu_codigo')]
#[Fillable(['usu_nombre', 'usu_correo', 'usu_clave'])]
#[Hidden(['usu_clave', 'usu_token_recordar'])]
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable;

    public const CREATED_AT = 'usu_fecha_creacion';

    public const UPDATED_AT = 'usu_fecha_actualizacion';

    /**
     * Columna de la contraseña que usa la autenticación.
     *
     * @var string
     */
    protected $authPasswordName = 'usu_clave';

    /**
     * Columna del token de "recordarme".
     *
     * @var string
     */
    protected $rememberTokenName = 'usu_token_recordar';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'usu_per_codigo' => 'integer',
            'usu_clave' => 'hashed',
            'usu_estado' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Perfil, $this>
     */
    public function perfil(): BelongsTo
    {
        return $this->belongsTo(Perfil::class, 'usu_per_codigo', 'per_codigo');
    }

    /**
     * Categorías turísticas que el usuario eligió como preferencias (RF-05).
     *
     * @return BelongsToMany<Categoria, $this>
     */
    public function preferencias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'tb_preferencia', 'pre_usu_codigo', 'pre_cat_codigo');
    }

    /**
     * @return HasMany<Informe, $this>
     */
    public function informes(): HasMany
    {
        return $this->hasMany(Informe::class, 'inf_usu_codigo', 'usu_codigo');
    }

    public function tipoPerfil(): TipoPerfil
    {
        return TipoPerfil::from($this->usu_per_codigo);
    }

    public function tienePerfil(TipoPerfil $perfil): bool
    {
        return $this->tipoPerfil() === $perfil;
    }

    /**
     * Correo al que se envía el enlace de recuperación de contraseña (RF-04).
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->usu_correo;
    }

    /**
     * Correo al que se envían las notificaciones por mail.
     */
    public function routeNotificationForMail(?Notification $notification = null): string
    {
        return $this->usu_correo;
    }
}
