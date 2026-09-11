<?php

use App\Exceptions\Autenticacion\EnlaceRestablecimientoInvalidoException;
use App\Services\AutenticacionService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Restablecer contraseña')] class extends Component
{
    public string $token = '';

    public string $correo = '';

    public string $clave = '';

    public string $clave_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->correo = (string) request()->query('email', '');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'correo' => ['required', 'string', 'email', 'max:255'],
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

    public function restablecerClave(AutenticacionService $autenticacion): void
    {
        $this->correo = Str::lower(trim($this->correo));
        $this->validate();

        try {
            $autenticacion->restablecerClave($this->correo, $this->clave, $this->token);
        } catch (EnlaceRestablecimientoInvalidoException $excepcion) {
            $this->addError('correo', $excepcion->getMessage());

            return;
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No pudimos restablecer tu contraseña. Inténtalo de nuevo en unos minutos.');

            return;
        }

        session()->flash('estado', 'Tu contraseña se restableció correctamente. Ya puedes iniciar sesión.');
        $this->redirect(route('login'), navigate: true);
    }
};
