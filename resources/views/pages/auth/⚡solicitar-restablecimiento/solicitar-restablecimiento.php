<?php

use App\Services\AutenticacionService;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Recuperar contraseña')] class extends Component
{
    public string $correo = '';

    public bool $enviado = false;

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'correo' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'correo' => 'correo electrónico',
        ];
    }

    public function solicitarRestablecimiento(AutenticacionService $autenticacion): void
    {
        $this->correo = Str::lower(trim($this->correo));
        $this->validate();

        try {
            $autenticacion->solicitarRestablecimiento($this->correo);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No pudimos enviar el enlace. Inténtalo de nuevo en unos minutos.');

            return;
        }

        $this->enviado = true;
    }
};
