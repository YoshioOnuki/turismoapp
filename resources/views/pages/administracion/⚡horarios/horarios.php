<?php

use App\Models\Horario;
use App\Services\HorarioService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Horarios y precios')] class extends Component
{
    /** @var list<array<string, mixed>> */
    public array $horarios = [];

    /** @var array<int, string> */
    public array $estaciones = [];

    public bool $mostrarFormulario = false;
    public ?int $codigo = null;
    public string $origenCodigo = '';
    public string $destinoCodigo = '';
    public string $servicio = '';
    public string $horaSalida = '';
    public string $horaLlegada = '';
    public string $precio = '';
    public ?string $mensaje = null;

    public function mount(HorarioService $horarios): void
    {
        Gate::authorize('administrar', Horario::class);
        $this->cargarDatos($horarios);
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'origenCodigo' => ['required', 'integer', $this->estacionActiva()],
            'destinoCodigo' => ['required', 'integer', 'different:origenCodigo', $this->estacionActiva()],
            'servicio' => ['required', 'string', 'max:60'],
            'horaSalida' => ['required', 'date_format:H:i'],
            'horaLlegada' => ['required', 'date_format:H:i'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999.99'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'origenCodigo' => 'estación de origen',
            'destinoCodigo' => 'estación de destino',
            'servicio' => 'servicio',
            'horaSalida' => 'hora de salida',
            'horaLlegada' => 'hora de llegada',
            'precio' => 'precio',
        ];
    }

    public function crear(): void
    {
        Gate::authorize('administrar', Horario::class);
        $this->reset(['codigo', 'origenCodigo', 'destinoCodigo', 'servicio', 'horaSalida', 'horaLlegada', 'precio']);
        $this->resetErrorBag();
        $this->mostrarFormulario = true;
    }

    public function editar(int $codigo): void
    {
        Gate::authorize('administrar', Horario::class);
        $horario = collect($this->horarios)->firstWhere('codigo', $codigo);
        abort_unless(is_array($horario), 404);
        $this->resetErrorBag();
        $this->codigo = $codigo;
        $this->origenCodigo = (string) $horario['origen_codigo'];
        $this->destinoCodigo = (string) $horario['destino_codigo'];
        $this->servicio = $horario['servicio'];
        $this->horaSalida = $horario['salida'];
        $this->horaLlegada = $horario['llegada'];
        $this->precio = (string) $horario['precio'];
        $this->mostrarFormulario = true;
    }

    public function guardar(HorarioService $horarios): void
    {
        Gate::authorize('administrar', Horario::class);
        $this->servicio = trim($this->servicio);
        $datos = $this->validate();

        try {
            $horarios->guardar($this->codigo, [
                'hor_est_codigo_origen' => (int) $datos['origenCodigo'],
                'hor_est_codigo_destino' => (int) $datos['destinoCodigo'],
                'hor_hora_salida' => $datos['horaSalida'].':00',
                'hor_hora_llegada' => $datos['horaLlegada'].':00',
                'hor_precio' => (float) $datos['precio'],
            ], $datos['servicio']);
            $this->mensaje = $this->codigo === null ? 'Horario registrado.' : 'Horario actualizado.';
            $this->mostrarFormulario = false;
            $this->cargarDatos($horarios);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible guardar el horario. Inténtalo nuevamente.');
        }
    }

    public function cambiarEstado(int $codigo, HorarioService $horarios): void
    {
        Gate::authorize('administrar', Horario::class);

        try {
            $horario = $horarios->cambiarEstado($codigo);
            $this->mensaje = $horario->hor_estado ? 'Horario reactivado.' : 'Horario dado de baja.';
            $this->cargarDatos($horarios);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible cambiar el estado del horario.');
        }
    }

    private function estacionActiva(): Exists
    {
        return Rule::exists('tb_estacion', 'est_codigo')->where(fn (Builder $consulta) => $consulta->where('est_estado', true));
    }

    private function cargarDatos(HorarioService $horarios): void
    {
        $this->horarios = $horarios->listar();
        $this->estaciones = $horarios->estaciones();
    }
};
