
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

    @if ($mostrarFormulario)
        <flux:card>
            <form wire:submit="guardar" class="space-y-6">
                <flux:heading size="lg">{{ $codigo === null ? 'Registrar zona' : 'Editar zona' }}</flux:heading>
                <div class="grid gap-5 md:grid-cols-2">
                    <flux:input wire:model="nombre" label="Nombre" />
                    <flux:select wire:model="estacionCodigo" label="Estación">
                        <flux:select.option value="">Selecciona una estación</flux:select.option>
                        @foreach ($estaciones as $valor => $etiqueta)
                            <flux:select.option :value="$valor">{{ $etiqueta }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="categoriaCodigo" label="Categoría">
                        <flux:select.option value="">Selecciona una categoría</flux:select.option>
                        @foreach ($categorias as $valor => $etiqueta)
                            <flux:select.option :value="$valor">{{ $etiqueta }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="dificultad" label="Dificultad">
                        <flux:select.option value="">Selecciona una dificultad</flux:select.option>
                        <flux:select.option value="baja">Baja</flux:select.option>
                        <flux:select.option value="media">Media</flux:select.option>
                        <flux:select.option value="alta">Alta</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="latitud" type="number" step="0.0000001" label="Latitud" />
                    <flux:input wire:model="longitud" type="number" step="0.0000001" label="Longitud" />
                    <flux:input wire:model="distancia" type="number" min="1" label="Distancia desde la estación (metros)" />
                    <flux:input wire:model="imagenes" type="file" multiple accept="image/*" label="Imágenes (máximo 5, 2 MB cada una)" />
                </div>
                <flux:textarea wire:model="descripcion" label="Descripción" rows="4" />
                <div class="flex justify-end gap-3">
                    <flux:button type="button" wire:click="$set('mostrarFormulario', false)" variant="ghost">Cancelar</flux:button>
                    <flux:button type="submit" wire:loading.attr="disabled" wire:target="guardar,imagenes" variant="primary">Guardar zona</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($zonas as $zona)
            <flux:card class="space-y-4" wire:key="zona-{{ $zona['codigo'] }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ $zona['nombre'] }}</flux:heading>
                        <flux:text>{{ $zona['categoria'] }} · {{ $zona['estacion'] }}</flux:text>
                    </div>
                    <flux:badge :color="$zona['estado'] ? 'green' : 'zinc'">{{ $zona['estado'] ? 'Activa' : 'Inactiva' }}</flux:badge>
                </div>
                @if ($zona['imagenes'] !== [])
                    <div class="flex gap-2 overflow-x-auto">
                        @foreach ($zona['imagenes'] as $imagen)
                            <img src="{{ $imagen['url'] }}" alt="{{ $zona['nombre'] }}" class="h-24 w-32 rounded-lg object-cover">
                        @endforeach
                    </div>
                @endif
                <flux:text>{{ $zona['descripcion'] }}</flux:text>
                <flux:text size="sm">{{ $zona['distancia'] }} m · Dificultad {{ $zona['dificultad'] }}</flux:text>
                <div class="flex flex-wrap justify-end gap-2">
                    <flux:button wire:click="editar({{ $zona['codigo'] }})" size="sm" icon="pencil-square">Editar</flux:button>
                    <flux:button wire:click="cambiarEstado({{ $zona['codigo'] }})" wire:confirm="¿Confirmas el cambio de estado de esta zona?" size="sm" :variant="$zona['estado'] ? 'danger' : 'primary'">
                        {{ $zona['estado'] ? 'Dar de baja' : 'Reactivar' }}
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <flux:callout icon="information-circle" heading="Aún no hay zonas turísticas registradas." />
        @endforelse
    </div>
</div>
