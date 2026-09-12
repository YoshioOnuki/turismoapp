<?php

use App\Models\Estacion;
use App\Models\Informe;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use App\Services\PlanificacionService;
use App\Services\InformeService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Planificar mi visita')] class extends Component
{
    /** @var array<int, string> */
    public array $estaciones = [];

    /** @var list<array<string, mixed>> */
    public array $zonas = [];

    /** @var list<array<string, mixed>> */
    public array $trenes = [];

    /** @var list<array<string, mixed>> */
    public array $clima = [];

    /** @var array{codigo: int, nombre: string, latitud: float, longitud: float}|null */
    public ?array $estacionConsultada = null;

    public string $estacionCodigo = '';
    public bool $busquedaRealizada = false;
    public ?string $mensajeInforme = null;

    public function mount(PlanificacionService $planificacion): void
    {
        Gate::authorize('seleccionar', Estacion::class);
        Gate::authorize('consultar', ZonaTuristica::class);
        $this->estaciones = $planificacion->estacionesActivas();
    }

    public function buscar(PlanificacionService $planificacion): void
    {
        Gate::authorize('seleccionar', Estacion::class);
        Gate::authorize('consultar', ZonaTuristica::class);
        $this->resetValidation();
        $this->reset(['zonas', 'trenes', 'clima', 'estacionConsultada', 'busquedaRealizada', 'mensajeInforme']);
        $datos = $this->validate([
            'estacionCodigo' => [
                'required',
                'integer',
                Rule::exists('tb_estacion', 'est_codigo')->where(fn (Builder $consulta) => $consulta->where('est_estado', true)),
            ],
        ]);

        try {
            $zonas = $planificacion->zonasDisponibles($this->usuario(), (int) $datos['estacionCodigo']);
            $informacion = $planificacion->informacionEstacion((int) $datos['estacionCodigo']);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible consultar la planificación. Inténtalo nuevamente.');

            return;
        }

        $this->zonas = $zonas;
        $this->estacionConsultada = $informacion['estacion'];
        $this->trenes = $informacion['trenes'];
        $this->clima = $informacion['clima'];
        $this->busquedaRealizada = true;
    }

    public function generarInforme(InformeService $informes): void
    {
        Gate::authorize('gestionarPropios', Informe::class);
        $datos = $this->validate([
            'estacionCodigo' => [
                'required',
                'integer',
                Rule::exists('tb_estacion', 'est_codigo')->where(fn (Builder $consulta) => $consulta->where('est_estado', true)),
            ],
        ]);

        try {
            $informes->generar($this->usuario(), (int) $datos['estacionCodigo']);
            $this->mensajeInforme = 'Informe guardado en tu historial.';
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
