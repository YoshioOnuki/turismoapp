<?php

namespace App\Http\Middleware;

use App\Enums\TipoPerfil;
use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPerfil
{
    /**
     * Arma la definición del middleware para los perfiles indicados, por ejemplo:
     * Route::middleware(VerificarPerfil::permitir(TipoPerfil::TravelGroup)).
     */
    public static function permitir(TipoPerfil ...$perfiles): string
    {
        $valores = array_map(fn (TipoPerfil $perfil): int => $perfil->value, $perfiles);

        return static::class.':'.implode(',', $valores);
    }

    /**
     * Deja pasar solo a los usuarios con alguno de los perfiles indicados (RF-03, RNF-12).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$perfiles): Response
    {
        $permitidos = array_map(fn (string $valor): TipoPerfil => TipoPerfil::from((int) $valor), $perfiles);
        $usuario = $request->user();

        abort_unless($usuario instanceof Usuario && in_array($usuario->tipoPerfil(), $permitidos, true), 403);

        return $next($request);
    }
}
