<?php

use App\Enums\Dificultad;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use App\Services\ZonaTuristicaService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Zonas turísticas')] class extends Component
{
    use WithFileUploads;

    /** @var list<array<string, mixed>> */
    public array $zonas = [];

    /** @var array<int, string> */
    public array $estaciones = [];

    /** @var array<int, string> */
    public array $categorias = [];

    /** @var list<mixed> */
    public array $imagenes = [];

    public bool $mostrarFormulario = false;
    public ?int $codigo = null;
    public string $nombre = '';
    public string $descripcion = '';
    public string $estacionCodigo = '';
    public string $categoriaCodigo = '';
    public string $latitud = '';
    public string $longitud = '';
    public string $distancia = '';
    public string $dificultad = '';
    public ?string $mensaje = null;

    public function mount(ZonaTuristicaService $zonas): void
    {
        Gate::authorize('administrar', ZonaTuristica::class);
        $this->cargarDatos($zonas);
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150', Rule::unique('tb_zona_turistica', 'zon_nombre')->ignore($this->codigo, 'zon_codigo')],
            'descripcion' => ['required', 'string', 'max:2000'],
            'estacionCodigo' => ['required', 'integer', 'exists:tb_estacion,est_codigo'],
            'categoriaCodigo' => ['required', 'integer', 'exists:tb_categoria,cat_codigo'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'distancia' => ['required', 'integer', 'min:1', 'max:100000'],
            'dificultad' => ['required', Rule::enum(Dificultad::class)],
            'imagenes' => ['array', 'max:5'],
            'imagenes.*' => ['image', 'max:2048'],
        ];
    }

    public function crear(): void
    {
        Gate::authorize('administrar', ZonaTuristica::class);
        $this->limpiarFormulario();
        $this->mostrarFormulario = true;
    }

    public function editar(int $codigo): void
    {
        Gate::authorize('administrar', ZonaTuristica::class);
        $zona = collect($this->zonas)->firstWhere('codigo', $codigo);
        abort_unless(is_array($zona), 404);
        $this->resetErrorBag();
        $this->codigo = $codigo;
        $this->nombre = $zona['nombre'];
        $this->descripcion = $zona['descripcion'];
        $this->estacionCodigo = (string) $zona['estacion_codigo'];
        $this->categoriaCodigo = (string) $zona['categoria_codigo'];
        $this->latitud = (string) $zona['latitud'];
        $this->longitud = (string) $zona['longitud'];
        $this->distancia = (string) $zona['distancia'];
        $this->dificultad = (string) $zona['dificultad_codigo'];
        $this->imagenes = [];
        $this->mostrarFormulario = true;
    }

    public function guardar(ZonaTuristicaService $zonas): void
    {
        Gate::authorize('administrar', ZonaTuristica::class);
        $datosValidados = $this->validate();

        try {
            $zonas->guardar($this->codigo, [
                'zon_est_codigo' => (int) $datosValidados['estacionCodigo'],
                'zon_cat_codigo' => (int) $datosValidados['categoriaCodigo'],
                'zon_nombre' => $datosValidados['nombre'],
                'zon_descripcion' => $datosValidados['descripcion'],
                'zon_latitud' => (float) $datosValidados['latitud'],
                'zon_longitud' => (float) $datosValidados['longitud'],
                'zon_distancia' => (int) $datosValidados['distancia'],
                'zon_dif_codigo' => (int) $datosValidados['dificultad'],
            ], $datosValidados['imagenes'], $this->usuario());
            $this->mensaje = $this->codigo === null ? 'Zona turística creada.' : 'Zona turística actualizada.';
            $this->mostrarFormulario = false;
            $this->cargarDatos($zonas);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible guardar la zona turística. Inténtalo nuevamente.');
        }
    }

    public function cambiarEstado(int $codigo, ZonaTuristicaService $zonas): void
    {
        Gate::authorize('administrar', ZonaTuristica::class);

        try {
            $zona = $zonas->cambiarEstado($codigo);
            $this->mensaje = $zona->zon_estado ? 'Zona turística reactivada.' : 'Zona turística dada de baja.';
            $this->cargarDatos($zonas);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible cambiar el estado de la zona turística.');
        }
    }

    private function usuario(): Usuario
    {
        $usuario = auth()->user();
        abort_unless($usuario instanceof Usuario, 403);

        return $usuario;
    }

    private function limpiarFormulario(): void
    {
        $this->reset(['codigo', 'nombre', 'descripcion', 'estacionCodigo', 'categoriaCodigo', 'latitud', 'longitud', 'distancia', 'dificultad', 'imagenes']);
        $this->resetErrorBag();
    }

    private function cargarDatos(ZonaTuristicaService $zonas): void
    {
        $this->zonas = $zonas->listar();
        $catalogos = $zonas->catalogos();
        $this->estaciones = $catalogos['estaciones'];
        $this->categorias = $catalogos['categorias'];
    }
};
