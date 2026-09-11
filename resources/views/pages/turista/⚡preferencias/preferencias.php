<?php

use App\Models\Categoria;
use App\Models\Usuario;
use App\Services\PreferenciaService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Mis preferencias')] class extends Component
{
    /** @var list<array{codigo: int, nombre: string}> */
    public array $categorias = [];

    /** @var list<int> */
    public array $categoriasSeleccionadas = [];

    public ?string $mensaje = null;

    public function mount(PreferenciaService $preferencias): void
    {
        Gate::authorize('seleccionar', Categoria::class);
        $this->cargarDatos($preferencias, $this->usuario());
    }

    public function guardar(PreferenciaService $preferencias): void
    {
        Gate::authorize('seleccionar', Categoria::class);
        $datos = $this->validate([
            'categoriasSeleccionadas' => ['array'],
            'categoriasSeleccionadas.*' => [
                'integer',
                'distinct',
                Rule::exists('tb_categoria', 'cat_codigo')->where(fn (Builder $consulta) => $consulta->where('cat_estado', true)),
            ],
        ]);

        try {
            $preferencias->guardar($this->usuario(), array_map('intval', $datos['categoriasSeleccionadas']));
            $this->mensaje = 'Tus preferencias se guardaron correctamente.';
            $this->cargarDatos($preferencias, $this->usuario());
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible guardar tus preferencias.');
        }
    }

    private function usuario(): Usuario
    {
        $usuario = auth()->user();
        abort_unless($usuario instanceof Usuario, 403);

        return $usuario;
    }

    private function cargarDatos(PreferenciaService $preferencias, Usuario $usuario): void
    {
        $datos = $preferencias->obtener($usuario);
        $this->categorias = $datos['categorias'];
        $this->categoriasSeleccionadas = $datos['seleccionadas'];
    }
};
