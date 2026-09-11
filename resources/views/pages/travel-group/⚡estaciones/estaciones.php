<?php

use App\Services\EstacionService;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Estaciones ferroviarias')] class extends Component
{
    /** @var list<array{codigo: int, codigo_externo: string, nombre: string, latitud: float, longitud: float, estado: bool, zonas_activas: int}> */
    public array $estaciones = [];

    public function mount(EstacionService $estaciones): void
    {
        $this->estaciones = $estaciones->listar();
    }
};
