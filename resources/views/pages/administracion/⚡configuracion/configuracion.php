<?php

use App\Models\Categoria;
use App\Models\Parametro;
use App\Services\ConfiguracionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Parámetros y categorías')] class extends Component
{
    /** @var list<array<string, mixed>> */
    public array $parametros = [];

    /** @var list<array<string, mixed>> */
    public array $categorias = [];

    public bool $mostrarParametro = false;
    public ?int $parametroCodigo = null;
    public string $parametroClave = '';
    public string $parametroDescripcion = '';
    public string $parametroValor = '';

    public bool $mostrarCategoria = false;
    public ?int $categoriaCodigo = null;
    public string $categoriaNombre = '';

    public ?string $mensaje = null;

    public function mount(ConfiguracionService $configuracion): void
    {
        Gate::authorize('administrar', Parametro::class);
        Gate::authorize('administrar', Categoria::class);
        $this->cargarDatos($configuracion);
    }

    public function editarParametro(int $codigo): void
    {
        $parametro = collect($this->parametros)->firstWhere('codigo', $codigo);
        abort_unless(is_array($parametro), 404);

        $this->resetErrorBag();
        $this->parametroCodigo = $codigo;
        $this->parametroClave = $parametro['clave'];
        $this->parametroDescripcion = $parametro['descripcion'] ?? '';
        $this->parametroValor = $parametro['valor'];
        $this->mostrarParametro = true;
    }

    public function guardarParametro(ConfiguracionService $configuracion): void
    {
        Gate::authorize('administrar', Parametro::class);
        $codigo = $this->parametroCodigo;
        abort_if($codigo === null, 404);
        $reglas = match ($this->parametroClave) {
            'distancia_maxima_caminable' => ['required', 'integer', 'min:1', 'max:100000'],
            'frecuencia_sincronizacion' => ['required', 'integer', 'min:1', 'max:720'],
            'velocidad_caminata' => ['required', 'numeric', 'min:0.1', 'max:20'],
            default => ['required', 'string', 'max:255'],
        };
        $datos = $this->validate(['parametroValor' => $reglas]);

        try {
            $configuracion->actualizarParametro($codigo, (string) $datos['parametroValor']);
            $this->mensaje = 'Parámetro actualizado correctamente.';
            $this->mostrarParametro = false;
            $this->cargarDatos($configuracion);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible actualizar el parámetro.');
        }
    }

    public function nuevaCategoria(): void
    {
        $this->resetErrorBag();
        $this->categoriaCodigo = null;
        $this->categoriaNombre = '';
        $this->mostrarCategoria = true;
    }

    public function editarCategoria(int $codigo): void
    {
        $categoria = collect($this->categorias)->firstWhere('codigo', $codigo);
        abort_unless(is_array($categoria), 404);

        $this->resetErrorBag();
        $this->categoriaCodigo = $codigo;
        $this->categoriaNombre = $categoria['nombre'];
        $this->mostrarCategoria = true;
    }

    public function guardarCategoria(ConfiguracionService $configuracion): void
    {
        Gate::authorize('administrar', Categoria::class);
        $datos = $this->validate([
            'categoriaNombre' => [
                'required',
                'string',
                'max:60',
                Rule::unique('tb_categoria', 'cat_nombre')->ignore($this->categoriaCodigo, 'cat_codigo'),
            ],
        ]);

        try {
            $configuracion->guardarCategoria($this->categoriaCodigo, $datos['categoriaNombre']);
            $this->mensaje = $this->categoriaCodigo === null ? 'Categoría creada correctamente.' : 'Categoría actualizada correctamente.';
            $this->mostrarCategoria = false;
            $this->cargarDatos($configuracion);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible guardar la categoría.');
        }
    }

    public function cambiarEstadoCategoria(int $codigo, ConfiguracionService $configuracion): void
    {
        Gate::authorize('administrar', Categoria::class);

        try {
            $categoria = $configuracion->cambiarEstadoCategoria($codigo);
            $this->mensaje = $categoria->cat_estado ? 'Categoría reactivada.' : 'Categoría dada de baja.';
            $this->cargarDatos($configuracion);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible cambiar el estado de la categoría.');
        }
    }

    private function cargarDatos(ConfiguracionService $configuracion): void
    {
        $datos = $configuracion->listar();
        $this->parametros = $datos['parametros'];
        $this->categorias = $datos['categorias'];
    }
};
