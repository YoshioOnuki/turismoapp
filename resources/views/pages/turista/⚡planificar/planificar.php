<?php

use App\Models\Categoria;
use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use App\Services\InformeService;
use App\Services\PlanificacionService;
use App\Services\PreferenciaService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Planificar mi visita')] class extends Component
{
    /** @var array<int, string> */
    public array $estaciones = [];

    /** @var list<array{codigo: int, nombre: string}> */
    public array $categorias = [];

    /** @var list<int> */
    public array $categoriasSeleccionadas = [];

    /** @var list<array<string, mixed>> */
    public array $zonas = [];

    /** @var list<array<string, mixed>> */
    public array $trenes = [];

    /** @var list<array<string, mixed>> */
    public array $clima = [];

    public bool $climaVigente = true;

    /** @var array{trenes: ?string, clima: ?string} */
    public array $actualizacion = ['trenes' => null, 'clima' => null];

    /** @var array{codigo: int, nombre: string, latitud: float, longitud: float}|null */
    #[Locked]
    public ?array $estacionConsultada = null;

    public string $estacionCodigo = '';
    public bool $busquedaRealizada = false;
    public ?string $mensajeInforme = null;

    #[Locked]
    public ?int $informeCodigo = null;

    public function mount(PlanificacionService $planificacion, PreferenciaService $preferencias): void
    {
        Gate::authorize('seleccionar', Estacion::class);
        Gate::authorize('consultar', ZonaTuristica::class);
        $this->estaciones = $planificacion->estacionesActivas();
        $datos = $preferencias->obtener($this->usuario());
        $this->categorias = $datos['categorias'];
        $this->categoriasSeleccionadas = $datos['seleccionadas'];
    }

    /**
     * Guarda las categorías marcadas como preferencias (RF-05) y consulta la estación
     * elegida (RF-06 a RF-09), para que el turista llegue a su informe en tres pasos (RNF-02).
     */
    public function buscar(PlanificacionService $planificacion, PreferenciaService $preferencias): void
    {
        Gate::authorize('seleccionar', Estacion::class);
        Gate::authorize('seleccionar', Categoria::class);
        Gate::authorize('consultar', ZonaTuristica::class);
        $this->resetValidation();
        $this->reset(['zonas', 'trenes', 'clima', 'climaVigente', 'actualizacion', 'estacionConsultada', 'busquedaRealizada', 'mensajeInforme', 'informeCodigo']);
        $datos = $this->validate(
            [
                'categoriasSeleccionadas' => ['required', 'array', 'min:1'],
                'categoriasSeleccionadas.*' => [
                    'integer',
                    'distinct',
                    Rule::exists('tb_categoria', 'cat_codigo')->where(fn (Builder $consulta) => $consulta->where('cat_estado', true)),
                ],
                'estacionCodigo' => [
                    'required',
                    'integer',
                    Rule::exists('tb_estacion', 'est_codigo')->where(fn (Builder $consulta) => $consulta->where('est_estado', true)),
                ],
            ],
            [
                'categoriasSeleccionadas.required' => 'Marca al menos una categoría turística.',
                'categoriasSeleccionadas.min' => 'Marca al menos una categoría turística.',
            ],
            [
                'categoriasSeleccionadas' => 'categorías',
                'estacionCodigo' => 'estación',
            ],
        );
        $estacionCodigo = (int) $datos['estacionCodigo'];

        try {
            $preferencias->guardar($this->usuario(), array_map('intval', $datos['categoriasSeleccionadas']));
            $zonas = $planificacion->zonasDisponibles($this->usuario(), $estacionCodigo);
            $informacion = $planificacion->informacionEstacion($estacionCodigo);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible consultar la planificación. Inténtalo nuevamente.');

            return;
        }

        $this->zonas = $zonas;
        $this->estacionConsultada = $informacion['estacion'];
        $this->trenes = $informacion['trenes'];
        $this->clima = $informacion['clima'];
        $this->climaVigente = $informacion['clima_vigente'];
        $this->actualizacion = $informacion['actualizacion'];
        $this->busquedaRealizada = true;
    }

    public function generarInforme(InformeService $informes): void
    {
        Gate::authorize('gestionarPropios', Informe::class);
        $this->resetErrorBag('general');

        if ($this->estacionConsultada === null) {
            $this->addError('general', 'Primero busca las zonas de una estación.');

            return;
        }

        try {
            $informe = $informes->generar($this->usuario(), $this->estacionConsultada['codigo']);
            $this->informeCodigo = $informe->getKey();
            $this->mensajeInforme = 'Informe guardado en tu historial. Ya puedes descargarlo.';
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible guardar el informe.');
        }
    }

    private function usuario(): Usuario
    {
        $usuario = auth()->user();
        abort_unless($usuario instanceof Usuario, 403);

        return $usuario;
    }
};
