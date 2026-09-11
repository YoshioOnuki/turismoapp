<?php

use App\Enums\ResultadoSincronizacion;
use App\Enums\TipoSincronizacion;
use App\Models\Bitacora;
use App\Models\Usuario;
use App\Services\SincronizacionService;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Sincronización de datos')] class extends Component
{
    /**
     * @var array<string, array{nombre: string, resultado: ?string, ultima_ejecucion: ?string, ultima_actualizacion: ?string, registros: int}>
     */
    public array $fuentes = [];

    public ?string $mensaje = null;

    public bool $sincronizacionConIncidencias = false;

    public function mount(SincronizacionService $sincronizacion): void
    {
        $this->cargarEstado($sincronizacion);
    }

    public function sincronizar(SincronizacionService $sincronizacion): void
    {
        $usuario = auth()->user();
        abort_unless($usuario instanceof Usuario, 403);
        $this->resetErrorBag();
        $this->mensaje = null;

        try {
            $resultados = $sincronizacion->ejecutar(TipoSincronizacion::Manual, $usuario);
            $this->sincronizacionConIncidencias = collect($resultados)
                ->contains(fn (Bitacora $bitacora): bool => $bitacora->bit_resultado === ResultadoSincronizacion::Error);
            $this->mensaje = $this->sincronizacionConIncidencias
                ? 'La sincronización terminó con incidencias. Se conservaron los últimos datos válidos.'
                : 'Las fuentes se sincronizaron correctamente.';
            $this->cargarEstado($sincronizacion);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible iniciar la sincronización. Inténtalo nuevamente.');
        }
    }

    private function cargarEstado(SincronizacionService $sincronizacion): void
    {
        $this->fuentes = $sincronizacion->estadoFuentes();
    }
};
