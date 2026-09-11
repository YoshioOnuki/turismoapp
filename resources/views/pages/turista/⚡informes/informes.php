<?php

use App\Models\Informe;
use App\Models\Usuario;
use App\Services\InformeService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Mis informes')] class extends Component
{
    /** @var list<array<string, mixed>> */
    public array $informes = [];

    public function mount(InformeService $informes): void
    {
        Gate::authorize('gestionarPropios', Informe::class);
        $this->informes = $informes->listar($this->usuario());
    }

    private function usuario(): Usuario
    {
        $usuario = auth()->user();
        abort_unless($usuario instanceof Usuario, 403);

        return $usuario;
    }
};
