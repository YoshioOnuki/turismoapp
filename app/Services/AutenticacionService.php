<?php

namespace App\Services;

use App\Enums\TipoPerfil;
use App\Exceptions\Autenticacion\CredencialesInvalidasException;
use App\Exceptions\Autenticacion\DemasiadosIntentosException;
use App\Models\Usuario;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AutenticacionService
{
    /**
     * Intentos fallidos permitidos por minuto para un mismo correo desde una misma IP.
     */
    public const INTENTOS_PERMITIDOS = 5;

    /**
     * Registra a un turista y le abre la sesión (RF-01). Los perfiles de Travel Group
     * Perú y del MTC los asigna el administrador (RF-19), nunca el propio usuario.
     */
    public function registrar(string $nombre, string $correo, string $clave): Usuario
    {
        $usuario = DB::transaction(function () use ($nombre, $correo, $clave): Usuario {
            $usuario = new Usuario([
                'usu_nombre' => $nombre,
                'usu_correo' => $correo,
                'usu_clave' => $clave,
            ]);
            $usuario->usu_per_codigo = TipoPerfil::UsuarioFinal->value;
            $usuario->save();

            return $usuario;
        });

        event(new Registered($usuario));

        Auth::login($usuario);
        session()->regenerate();

        return $usuario;
    }

    /**
     * Abre la sesión de un usuario activo (RF-02). Tras cinco intentos fallidos con el
     * mismo correo desde la misma IP, bloquea los intentos durante un minuto.
     *
     * @throws DemasiadosIntentosException
     * @throws CredencialesInvalidasException
     */
    public function iniciarSesion(string $correo, string $clave, bool $recordar, string $ip): void
    {
        $llave = Str::transliterate(Str::lower($correo).'|'.$ip);

        if (RateLimiter::tooManyAttempts($llave, self::INTENTOS_PERMITIDOS)) {
            throw new DemasiadosIntentosException(RateLimiter::availableIn($llave));
        }

        // Laravel exige la llave "password"; la columna real (usu_clave) la define Usuario::$authPasswordName.
        $credenciales = [
            'usu_correo' => $correo,
            'password' => $clave,
            'usu_estado' => true,
        ];

        if (! Auth::attempt($credenciales, $recordar)) {
            RateLimiter::hit($llave);

            throw new CredencialesInvalidasException;
        }

        RateLimiter::clear($llave);
        session()->regenerate();
    }

    /**
     * Cierra la sesión actual e invalida sus datos (RF-02).
     */
    public function cerrarSesion(): void
    {
        Auth::guard('web')->logout();

        session()->invalidate();
        session()->regenerateToken();
    }
}
