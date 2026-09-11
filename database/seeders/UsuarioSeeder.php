<?php

namespace Database\Seeders;

use App\Enums\TipoPerfil;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    /**
     * Un usuario de demostración por perfil. Todos usan la contraseña "password".
     */
    public function run(): void
    {
        Usuario::factory()->conPerfil(TipoPerfil::AdministradorMtc)->create([
            'usu_nombre' => 'Administrador MTC',
            'usu_correo' => 'admin@turismoapp.test',
        ]);

        Usuario::factory()->conPerfil(TipoPerfil::TravelGroup)->create([
            'usu_nombre' => 'Travel Group Perú',
            'usu_correo' => 'travelgroup@turismoapp.test',
        ]);

        Usuario::factory()->conPerfil(TipoPerfil::UsuarioFinal)->create([
            'usu_nombre' => 'Turista de demostración',
            'usu_correo' => 'turista@turismoapp.test',
        ]);
    }
}
