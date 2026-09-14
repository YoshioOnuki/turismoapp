<?php

use App\Models\Usuario;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Mi panel')] class extends Component
{
    #[Locked]
    public int $preferenciasTotal = 0;

    #[Locked]
    public int $informesTotal = 0;

    public function mount(): void
    {
        $usuario = auth()->user();
        abort_unless($usuario instanceof Usuario, 403);

        $this->preferenciasTotal = $usuario->preferencias()->where('cat_estado', true)->count();
        $this->informesTotal = $usuario->informes()->count();
    }
};
