
<div class="space-y-8">
    <div>
        <flux:link :href="route('turista.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
        <flux:heading size="xl" class="mt-4">Mis preferencias</flux:heading>
        <flux:text class="mt-2">Selecciona las categorías que deseas considerar al planificar tu visita.</flux:text>
    </div>

    @if ($mensaje)
        <flux:callout variant="success" icon="check-circle" :heading="$mensaje" />
    @endif
    @error('general')
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
    @enderror

    <form wire:submit="guardar" class="space-y-5">
        <flux:card>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Seleccionar</flux:table.column>
                    <flux:table.column>Categoría turística</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($categorias as $categoria)
                        <flux:table.row :key="$categoria['codigo']">
                            <flux:table.cell>
                                <flux:checkbox wire:model="categoriasSeleccionadas" :value="$categoria['codigo']" :aria-label="'Seleccionar '.$categoria['nombre']" />
                            </flux:table.cell>
                            <flux:table.cell variant="strong">{{ $categoria['nombre'] }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row><flux:table.cell colspan="2">No hay categorías activas disponibles.</flux:table.cell></flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
        <div class="flex justify-end">
            <flux:button type="submit" wire:loading.attr="disabled" wire:target="guardar" variant="primary" icon="check">Guardar preferencias</flux:button>
        </div>
    </form>
</div>
