<?php

use App\Exceptions\Autenticacion\CredencialesInvalidasException;
use App\Exceptions\Autenticacion\DemasiadosIntentosException;
use App\Services\AutenticacionService;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Iniciar sesión')] class extends Component
{
    public string $correo = '';

    public string $clave = '';

    public bool $recordar = false;

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'correo' => ['required', 'string', 'email'],
            'clave' => ['required', 'string'],
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

    public function iniciarSesion(AutenticacionService $autenticacion): void
    {
        $this->correo = Str::lower(trim($this->correo));
        $this->validate();

        try {
            $autenticacion->iniciarSesion($this->correo, $this->clave, $this->recordar, (string) request()->ip());
        } catch (CredencialesInvalidasException) {
            $this->addError('correo', __('auth.failed'));

            return;
        } catch (DemasiadosIntentosException $excepcion) {
            $this->addError('correo', __('auth.throttle', ['seconds' => $excepcion->segundos]));

            return;
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No pudimos iniciar tu sesión. Inténtalo de nuevo en unos minutos.');

            return;
        }

        $this->redirectIntended(route('inicio'), navigate: true);
    }
};
