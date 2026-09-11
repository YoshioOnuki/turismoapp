<?php

use App\Models\Informe;
use App\Services\ReporteService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Reporte de uso')] class extends Component
{
    /** @var list<array<string, mixed>> */
    public array $estaciones = [];

    /** @var list<array<string, mixed>> */
    public array $categorias = [];

    public function mount(ReporteService $reportes): void
    {
        Gate::authorize('consultarUso', Informe::class);
        $datos = $reportes->uso();
        $this->estaciones = $datos['estaciones'];
        $this->categorias = $datos['categorias'];
    }
};
