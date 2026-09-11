<?php

namespace Database\Factories;

use App\Enums\TipoPerfil;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $clave;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usu_per_codigo' => TipoPerfil::UsuarioFinal->value,
            'usu_nombre' => fake()->name(),
            'usu_correo' => fake()->unique()->safeEmail(),
            'usu_clave' => static::$clave ??= Hash::make('password'),
            'usu_estado' => true,
            'usu_token_recordar' => Str::random(10),
        ];
    }

    /**
     * Asigna al usuario el perfil indicado.
     */
    public function conPerfil(TipoPerfil $perfil): static
    {
        return $this->state(fn (array $attributes) => [
            'usu_per_codigo' => $perfil->value,
        ]);
    }
}
