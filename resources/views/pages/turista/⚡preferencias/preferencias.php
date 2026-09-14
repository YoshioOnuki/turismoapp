<?php

use App\Models\Categoria;
use App\Models\Usuario;
use App\Services\PreferenciaService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Mis preferencias')] class extends Component
{
    /** @var list<array{codigo: int, nombre: string, descripcion: string, icono: string, clasesIcono: string, clasesSeleccion: string}> */
    #[Locked]
    public array $categorias = [];

    /** @var list<int> */
    public array $categoriasSeleccionadas = [];

    #[Locked]
    public bool $configuracionInicial = true;

    public function mount(PreferenciaService $preferencias): void
    {
        Gate::authorize('seleccionar', Categoria::class);
        $this->cargarDatos($preferencias, $this->usuario());
        $this->configuracionInicial = $this->categoriasSeleccionadas === [];
    }

    public function guardar(PreferenciaService $preferencias): void
    {
        Gate::authorize('seleccionar', Categoria::class);
        $datos = $this->validate([
            'categoriasSeleccionadas' => ['required', 'array', 'min:1'],
            'categoriasSeleccionadas.*' => [
                'integer',
                'distinct',
                Rule::exists('tb_categoria', 'cat_codigo')->where(fn (Builder $consulta) => $consulta->where('cat_estado', true)),
            ],
        ]);

        try {
            $preferencias->guardar($this->usuario(), array_map('intval', $datos['categoriasSeleccionadas']));
            session()->flash('estado', 'Tus preferencias se guardaron correctamente. Ya puedes empezar a planificar.');
            $this->redirect(route('turista.panel'), navigate: true);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->addError('general', 'No fue posible guardar tus preferencias.');
        }
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'categoriasSeleccionadas.required' => 'Selecciona al menos una categoría para continuar.',
            'categoriasSeleccionadas.min' => 'Selecciona al menos una categoría para continuar.',
        ];
    }

    private function usuario(): Usuario
    {
        $usuario = auth()->user();
        abort_unless($usuario instanceof Usuario, 403);

        return $usuario;
    }

    private function cargarDatos(PreferenciaService $preferencias, Usuario $usuario): void
    {
        $datos = $preferencias->obtener($usuario);
        $this->categorias = array_map($this->presentarCategoria(...), $datos['categorias']);
        $this->categoriasSeleccionadas = $datos['seleccionadas'];
    }

    /**
     * @param  array{codigo: int, nombre: string}  $categoria
     * @return array{codigo: int, nombre: string, descripcion: string, icono: string, clasesIcono: string, clasesSeleccion: string}
     */
    private function presentarCategoria(array $categoria): array
    {
        $presentacion = match (Str::lower($categoria['nombre'])) {
            'naturaleza' => [
                'descripcion' => 'Paisajes, biodiversidad y espacios naturales para respirar y desconectarte.',
                'icono' => 'sparkles',
                'clasesIcono' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300',
                'clasesSeleccion' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50/80 peer-checked:ring-2 peer-checked:ring-emerald-500/20 dark:peer-checked:border-emerald-500 dark:peer-checked:bg-emerald-950/30',
            ],
            'historia' => [
                'descripcion' => 'Museos, patrimonio y lugares que cuentan la memoria de cada destino.',
                'icono' => 'book-open',
                'clasesIcono' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300',
                'clasesSeleccion' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50/80 peer-checked:ring-2 peer-checked:ring-amber-500/20 dark:peer-checked:border-amber-500 dark:peer-checked:bg-amber-950/30',
            ],
            'aventura' => [
                'descripcion' => 'Recorridos activos, miradores y experiencias para salir de la rutina.',
                'icono' => 'fire',
                'clasesIcono' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/60 dark:text-orange-300',
                'clasesSeleccion' => 'peer-checked:border-orange-500 peer-checked:bg-orange-50/80 peer-checked:ring-2 peer-checked:ring-orange-500/20 dark:peer-checked:border-orange-500 dark:peer-checked:bg-orange-950/30',
            ],
            'gastronomía', 'gastronomia' => [
                'descripcion' => 'Sabores locales, mercados y experiencias para conocer el destino desde su cocina.',
                'icono' => 'cake',
                'clasesIcono' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300',
                'clasesSeleccion' => 'peer-checked:border-rose-500 peer-checked:bg-rose-50/80 peer-checked:ring-2 peer-checked:ring-rose-500/20 dark:peer-checked:border-rose-500 dark:peer-checked:bg-rose-950/30',
            ],
            default => [
                'descripcion' => 'Incluye esta categoría para descubrir recomendaciones que coincidan contigo.',
                'icono' => 'globe-alt',
                'clasesIcono' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/60 dark:text-sky-300',
                'clasesSeleccion' => 'peer-checked:border-sky-500 peer-checked:bg-sky-50/80 peer-checked:ring-2 peer-checked:ring-sky-500/20 dark:peer-checked:border-sky-500 dark:peer-checked:bg-sky-950/30',
            ],
        };

        return [...$categoria, ...$presentacion];
    }
};
