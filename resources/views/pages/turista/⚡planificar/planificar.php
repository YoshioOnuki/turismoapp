<?php

use App\Models\Estacion;
use App\Models\Usuario;
use App\Models\ZonaTuristica;
use App\Services\PlanificacionService;
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

    public string $estacionCodigo = '';
    public bool $busquedaRealizada = false;

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
        $datos = $this->validate([
            'estacionCodigo' => [
                'required',
                'integer',
                Rule::exists('tb_estacion', 'est_codigo')->where(fn (Builder $consulta) => $consulta->where('est_estado', true)),
            ],
        ]);

        $this->zonas = $planificacion->zonasDisponibles($this->usuario(), (int) $datos['estacionCodigo']);
        $this->busquedaRealizada = true;
    }

    private function usuario(): Usuario
    {
        $usuario = auth()->user();
        abort_unless($usuario instanceof Usuario, 403);

        return $usuario;
    }
};
