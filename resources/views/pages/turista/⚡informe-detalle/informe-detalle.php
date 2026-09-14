<?php

use App\Models\Informe;
use App\Services\InformeExportacionService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Detalle del informe')] class extends Component
{
    #[Locked]
    public int $informeCodigo;

    #[Locked]
    public string $estacion;

    /** @var array{codigo: int, nombre: string, latitud: float, longitud: float} */
    #[Locked]
    public array $estacionMapa = [];

    /** @var list<string> */
    #[Locked]
    public array $categorias = [];

    #[Locked]
    public string $fecha;

    /** @var list<array<string, mixed>> */
    #[Locked]
    public array $zonas = [];

    /** @var list<array<string, mixed>> */
    #[Locked]
    public array $trenes = [];

    /** @var list<array<string, mixed>> */
    #[Locked]
    public array $clima = [];

    #[Locked]
    public bool $climaVigente = true;

    /** @var array{trenes: ?string, clima: ?string} */
    #[Locked]
    public array $actualizacion = ['trenes' => null, 'clima' => null];

    /** @var array{zonas: int, tiempo_minimo: ?int, distancia_minima: ?int, precio_desde: ?float} */
    #[Locked]
    public array $resumen = [];

    public function mount(Informe $informe, InformeExportacionService $exportacion): void
    {
        Gate::authorize('ver', $informe);
        $detalle = $exportacion->preparar($informe);

        $this->informeCodigo = $informe->getKey();
        $this->estacion = $detalle['estacion'];
        $this->estacionMapa = $detalle['estacionMapa'];
        $this->categorias = $detalle['categoriasLista'];
        $this->fecha = $detalle['fecha'];
        $this->zonas = $detalle['zonas'];
        $this->trenes = $detalle['trenes'];
        $this->clima = $detalle['clima'];
        $this->climaVigente = $detalle['climaVigente'];
        $this->actualizacion = $detalle['actualizacion'];
        $this->resumen = $detalle['resumen'];
    }
};
