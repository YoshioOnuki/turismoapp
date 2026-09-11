<?php

use App\Models\Estacion;
use App\Services\ReporteService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Zonas por estación')] class extends Component
{
    /** @var list<array<string, mixed>> */
    public array $estaciones = [];

    public function mount(ReporteService $reportes): void
    {
        Gate::authorize('consultarReporte', Estacion::class);
        $this->estaciones = $reportes->zonasPorEstacion();
    }
};
