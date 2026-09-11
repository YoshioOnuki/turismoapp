<?php

use App\Models\Usuario;
use App\Services\AutenticacionService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Crear cuenta')] class extends Component
{
    public string $nombre = '';

    public string $correo = '';

    public string $clave = '';

    public string $clave_confirmation = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['required', 'string', 'email', 'max:255', Rule::unique(Usuario::class, 'usu_correo')],
            'clave' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'correo' => 'correo electrónico',
            'clave' => 'contraseña',
        ];
    }

    public function registrar(AutenticacionService $autenticacion): void
    {
        $this->nombre = trim($this->nombre);
        $this->correo = Str::lower(trim($this->correo));
        $this->validate();

        try {
            $autenticacion->registrar($this->nombre, $this->correo, $this->clave);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No pudimos crear tu cuenta. Inténtalo de nuevo en unos minutos.');

            return;
        }

        $this->redirectIntended(route('inicio'), navigate: true);
    }
};
