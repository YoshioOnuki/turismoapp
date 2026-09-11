<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:link :href="route('travel-group.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
            <flux:heading size="xl" class="mt-4">Zonas turísticas</flux:heading>
            <flux:text class="mt-2">Administra los atractivos cercanos a cada estación ferroviaria.</flux:text>
        </div>
        <flux:button wire:click="crear" variant="primary" icon="plus">Nueva zona</flux:button>
    </div>

    @if ($mensaje)
        <flux:callout variant="success" icon="check-circle" :heading="$mensaje" />
    @endif
    @error('general')
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
    @enderror

    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Zona</flux:table.column>
                <flux:table.column>Estación</flux:table.column>
                <flux:table.column>Categoría</flux:table.column>
                <flux:table.column>Ruta</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($zonas as $zona)
                    <flux:table.row :key="$zona['codigo']">
                        <flux:table.cell variant="strong">
                            <div class="flex items-center gap-3">
                                @if ($zona['imagenes'] !== [])
                                    <img src="{{ $zona['imagenes'][0]['url'] }}" alt="" class="size-10 rounded-lg object-cover">
                                @else
                                    <div class="grid size-10 place-items-center rounded-lg bg-zinc-100 dark:bg-zinc-800"><flux:icon icon="photo" class="size-5" /></div>
                                @endif
                                <span>{{ $zona['nombre'] }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $zona['estacion'] }}</flux:table.cell>
                        <flux:table.cell>{{ $zona['categoria'] }}</flux:table.cell>
                        <flux:table.cell>{{ $zona['distancia'] }} m · {{ ucfirst($zona['dificultad']) }}</flux:table.cell>
                        <flux:table.cell><flux:badge :color="$zona['estado'] ? 'green' : 'zinc'">{{ $zona['estado'] ? 'Activa' : 'Inactiva' }}</flux:badge></flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                <flux:button wire:click="editar({{ $zona['codigo'] }})" size="sm" icon="pencil-square">Editar</flux:button>
                                <flux:button wire:click="cambiarEstado({{ $zona['codigo'] }})" wire:confirm="¿Confirmas el cambio de estado de esta zona?" size="sm" :variant="$zona['estado'] ? 'danger' : 'primary'">{{ $zona['estado'] ? 'Dar de baja' : 'Reactivar' }}</flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row><flux:table.cell colspan="6">Aún no hay zonas turísticas registradas.</flux:table.cell></flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal wire:model="mostrarFormulario" class="md:w-[48rem]" scroll="body">
        <form wire:submit="guardar" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $codigo === null ? 'Registrar zona' : 'Editar zona' }}</flux:heading>
                <flux:text class="mt-2">Completa la ubicación y los datos turísticos.</flux:text>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="nombre" label="Nombre" />
                <flux:select wire:model="estacionCodigo" label="Estación"><flux:select.option value="">Selecciona una estación</flux:select.option>@foreach ($estaciones as $valor => $etiqueta)<flux:select.option :value="$valor">{{ $etiqueta }}</flux:select.option>@endforeach</flux:select>
                <flux:select wire:model="categoriaCodigo" label="Categoría"><flux:select.option value="">Selecciona una categoría</flux:select.option>@foreach ($categorias as $valor => $etiqueta)<flux:select.option :value="$valor">{{ $etiqueta }}</flux:select.option>@endforeach</flux:select>
                <flux:select wire:model="dificultad" label="Dificultad"><flux:select.option value="">Selecciona una dificultad</flux:select.option><flux:select.option value="baja">Baja</flux:select.option><flux:select.option value="media">Media</flux:select.option><flux:select.option value="alta">Alta</flux:select.option></flux:select>
                <flux:input wire:model="latitud" type="number" step="0.0000001" label="Latitud" />
                <flux:input wire:model="longitud" type="number" step="0.0000001" label="Longitud" />
                <flux:input wire:model="distancia" type="number" min="1" label="Distancia desde la estación (metros)" />
                <flux:input wire:model="imagenes" type="file" multiple accept="image/*" label="Imágenes (máximo 5, 2 MB cada una)" />
            </div>
            <flux:textarea wire:model="descripcion" label="Descripción" rows="4" />
            <div class="flex justify-end gap-3">
                <flux:modal.close><flux:button type="button" variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" wire:loading.attr="disabled" wire:target="guardar,imagenes" variant="primary">Guardar zona</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
