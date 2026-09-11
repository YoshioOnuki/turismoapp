<?php

use App\Enums\TipoPerfil;
use App\Models\Usuario;
use App\Services\UsuarioAdministracionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Gestión de usuarios')] class extends Component
{
    /** @var list<array<string, mixed>> */
    public array $usuarios = [];
    public bool $mostrarFormulario = false;
    public string $nombre = '';
    public string $correo = '';
    public string $clave = '';
    public string $claveConfirmation = '';
    public string $perfil = '';
    public ?string $mensaje = null;

    public function mount(UsuarioAdministracionService $usuarios): void
    {
        Gate::authorize('administrar', Usuario::class);
        $this->usuarios = $usuarios->listar();
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['required', 'email', 'max:255', 'unique:tb_usuario,usu_correo'],
            'clave' => ['required', 'same:claveConfirmation', Password::min(8)->letters()->numbers()],
            'claveConfirmation' => ['required'],
            'perfil' => ['required', Rule::enum(TipoPerfil::class)],
        ];
    }

    public function nuevo(): void
    {
        $this->reset(['nombre', 'correo', 'clave', 'claveConfirmation', 'perfil']);
        $this->resetErrorBag();
        $this->mostrarFormulario = true;
    }

    public function crear(UsuarioAdministracionService $usuarios): void
    {
        Gate::authorize('administrar', Usuario::class);
        $datos = $this->validate();

        try {
            $usuarios->crear($datos['nombre'], $datos['correo'], $datos['clave'], TipoPerfil::from((int) $datos['perfil']));
            $this->mensaje = 'Usuario creado correctamente.';
            $this->mostrarFormulario = false;
            $this->reset(['nombre', 'correo', 'clave', 'claveConfirmation', 'perfil']);
            $this->usuarios = $usuarios->listar();
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible guardar el usuario.');
        }
    }

    public function cambiarPerfil(int $codigo, int $perfil, UsuarioAdministracionService $usuarios): void
    {
        Gate::authorize('administrar', Usuario::class);
        $this->ejecutarCambio($usuarios, fn (Usuario $administrador) => $usuarios->cambiarPerfil($codigo, TipoPerfil::from($perfil), $administrador), 'Perfil actualizado.');
    }

    public function cambiarEstado(int $codigo, UsuarioAdministracionService $usuarios): void
    {
        Gate::authorize('administrar', Usuario::class);
        $this->ejecutarCambio($usuarios, fn (Usuario $administrador) => $usuarios->cambiarEstado($codigo, $administrador), 'Estado actualizado.');
    }

    private function ejecutarCambio(UsuarioAdministracionService $usuarios, Closure $accion, string $mensaje): void
    {
        $administrador = auth()->user();
        abort_unless($administrador instanceof Usuario, 403);

        try {
            $accion($administrador);
            $this->mensaje = $mensaje;
            $this->usuarios = $usuarios->listar();
        } catch (\DomainException $excepcion) {
            $this->addError('general', $excepcion->getMessage());
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible actualizar el usuario.');
        }
    }
};
