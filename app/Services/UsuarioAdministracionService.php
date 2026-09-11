<?php

namespace App\Services;

use App\Enums\TipoPerfil;
use App\Models\Usuario;
use DomainException;
use Illuminate\Support\Facades\DB;

class UsuarioAdministracionService
{
    /** @return list<array{codigo: int, nombre: string, correo: string, perfil: int, perfil_nombre: string, estado: bool}> */
    public function listar(): array
    {
        return Usuario::query()->orderBy('usu_nombre')->get()->map(fn (Usuario $usuario): array => [
            'codigo' => $usuario->getKey(),
            'nombre' => $usuario->usu_nombre,
            'correo' => $usuario->usu_correo,
            'perfil' => $usuario->usu_per_codigo,
            'perfil_nombre' => $usuario->tipoPerfil()->etiqueta(),
            'estado' => $usuario->usu_estado,
        ])->all();
    }

    public function crear(string $nombre, string $correo, string $clave, TipoPerfil $perfil): Usuario
    {
        return DB::transaction(function () use ($nombre, $correo, $clave, $perfil): Usuario {
            $usuario = new Usuario([
                'usu_nombre' => $nombre,
                'usu_correo' => $correo,
                'usu_clave' => $clave,
            ]);
            $usuario->usu_per_codigo = $perfil->value;
            $usuario->usu_estado = true;
            $usuario->save();

            return $usuario;
        });
    }

    public function cambiarPerfil(int $codigo, TipoPerfil $perfil, Usuario $administrador): Usuario
    {
        $this->impedirAutogestion($codigo, $administrador);

        return DB::transaction(function () use ($codigo, $perfil): Usuario {
            $usuario = Usuario::query()->findOrFail($codigo);
            $usuario->usu_per_codigo = $perfil->value;
            $usuario->save();

            return $usuario;
        });
    }

    public function cambiarEstado(int $codigo, Usuario $administrador): Usuario
    {
        $this->impedirAutogestion($codigo, $administrador);

        return DB::transaction(function () use ($codigo): Usuario {
            $usuario = Usuario::query()->findOrFail($codigo);
            $usuario->usu_estado = ! $usuario->usu_estado;
            $usuario->save();

            return $usuario;
        });
    }

    private function impedirAutogestion(int $codigo, Usuario $administrador): void
    {
        if ($codigo === $administrador->getKey()) {
            throw new DomainException('No puedes cambiar tu propio perfil o estado.');
        }
    }
}
