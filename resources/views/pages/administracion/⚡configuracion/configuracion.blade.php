
<div class="space-y-8">
    <div>
        <flux:link :href="route('administracion.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
        <flux:heading size="xl" class="mt-4">Parámetros y categorías</flux:heading>
        <flux:text class="mt-2">Configura las reglas generales y el catálogo turístico del sistema.</flux:text>
    </div>

    @if ($mensaje)
        <flux:callout variant="success" icon="check-circle" :heading="$mensaje" />
    @endif
    @error('general')
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
    @enderror

    <section class="space-y-4">
        <div>
            <flux:heading size="lg">Parámetros generales</flux:heading>
            <flux:text class="mt-1">Estos valores se aplican en cálculos y procesos automáticos.</flux:text>
        </div>
        <flux:card>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Parámetro</flux:table.column>
                    <flux:table.column>Clave técnica</flux:table.column>
                    <flux:table.column>Valor</flux:table.column>
                    <flux:table.column align="end">Acciones</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($parametros as $parametro)
                        <flux:table.row :key="'parametro-'.$parametro['codigo']">
                            <flux:table.cell variant="strong">
                                <div class="flex max-w-md flex-col gap-1 whitespace-normal">
                                    <span>{{ $parametro['descripcion'] }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell><code>{{ $parametro['clave'] }}</code></flux:table.cell>
                            <flux:table.cell>{{ $parametro['valor'] }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button wire:click="editarParametro({{ $parametro['codigo'] }})" size="sm" icon="pencil-square">Editar</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </section>

    <section class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <flux:heading size="lg">Categorías turísticas</flux:heading>
                <flux:text class="mt-1">Clasifica las zonas y preferencias de los turistas.</flux:text>
            </div>
            <flux:button wire:click="nuevaCategoria" variant="primary" icon="plus">Nueva categoría</flux:button>
        </div>
        <flux:card>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nombre</flux:table.column>
                    <flux:table.column>Zonas asociadas</flux:table.column>
                    <flux:table.column>Estado</flux:table.column>
                    <flux:table.column align="end">Acciones</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($categorias as $categoria)
                        <flux:table.row :key="'categoria-'.$categoria['codigo']">
                            <flux:table.cell variant="strong">{{ $categoria['nombre'] }}</flux:table.cell>
                            <flux:table.cell>{{ $categoria['zonas'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$categoria['estado'] ? 'green' : 'zinc'">{{ $categoria['estado'] ? 'Activa' : 'Inactiva' }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:button wire:click="editarCategoria({{ $categoria['codigo'] }})" size="sm" icon="pencil-square">Editar</flux:button>
                                    <flux:button wire:click="cambiarEstadoCategoria({{ $categoria['codigo'] }})" wire:confirm="¿Confirmas el cambio de estado de esta categoría?" size="sm" :variant="$categoria['estado'] ? 'danger' : 'primary'">
                                        {{ $categoria['estado'] ? 'Dar de baja' : 'Reactivar' }}
                                    </flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </section>

    <flux:modal wire:model="mostrarParametro" class="md:w-[34rem]">
        <form wire:submit="guardarParametro" class="space-y-6">
            <div>
                <flux:heading size="lg">Editar parámetro</flux:heading>
                <flux:text class="mt-2">{{ $parametroDescripcion }}</flux:text>
            </div>
            <flux:input wire:model="parametroValor" label="Valor" />
            <div class="flex justify-end gap-3">
                <flux:modal.close><flux:button type="button" variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" wire:loading.attr="disabled" wire:target="guardarParametro" variant="primary">Guardar parámetro</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="mostrarCategoria" class="md:w-[34rem]">
        <form wire:submit="guardarCategoria" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $categoriaCodigo === null ? 'Nueva categoría' : 'Editar categoría' }}</flux:heading>
                <flux:text class="mt-2">Define el nombre visible en las preferencias y zonas.</flux:text>
            </div>
            <flux:input wire:model="categoriaNombre" label="Nombre" />
            <div class="flex justify-end gap-3">
                <flux:modal.close><flux:button type="button" variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" wire:loading.attr="disabled" wire:target="guardarCategoria" variant="primary">Guardar categoría</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
