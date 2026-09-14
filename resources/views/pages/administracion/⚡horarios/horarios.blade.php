<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:link :href="route('administracion.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
            <flux:heading size="xl" class="mt-4">Horarios y precios</flux:heading>
            <flux:text class="mt-2">Mantén los servicios de tren con sus horarios, tiempos de viaje y precios.</flux:text>
        </div>
        <flux:button wire:click="crear" variant="primary" icon="plus">Nuevo horario</flux:button>
    </div>

    <flux:callout icon="information-circle" heading="Los horarios con código de PeruRail se actualizan en cada sincronización.">
        <flux:callout.text>Usa esta pantalla para correcciones puntuales o para registrar servicios adicionales. Los cambios sobre un horario de PeruRail se reemplazan en la siguiente sincronización.</flux:callout.text>
    </flux:callout>

    @if ($mensaje)
        <flux:callout variant="success" icon="check-circle" :heading="$mensaje" />
    @endif
    @error('general')
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
    @enderror

    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Servicio</flux:table.column>
                <flux:table.column>Ruta</flux:table.column>
                <flux:table.column>Horario</flux:table.column>
                <flux:table.column>Precio</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($horarios as $horario)
                    <flux:table.row :key="$horario['codigo']">
                        <flux:table.cell variant="strong">
                            <div class="flex flex-col gap-1">
                                <span>{{ $horario['servicio'] }}</span>
                                <span class="font-normal text-zinc-500 dark:text-zinc-400">{{ $horario['codigo_externo'] }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-normal">{{ $horario['origen'] }} → {{ $horario['destino'] }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col gap-1">
                                <span>{{ $horario['salida'] }} – {{ $horario['llegada'] }}</span>
                                <span class="text-zinc-500 dark:text-zinc-400">{{ $horario['duracion'] }} de viaje</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>S/ {{ $horario['precio'] }}</flux:table.cell>
                        <flux:table.cell><flux:badge :color="$horario['estado'] ? 'green' : 'zinc'">{{ $horario['estado'] ? 'Activo' : 'Inactivo' }}</flux:badge></flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                <flux:button wire:click="editar({{ $horario['codigo'] }})" size="sm" icon="pencil-square">Editar</flux:button>
                                <flux:button wire:click="cambiarEstado({{ $horario['codigo'] }})" wire:confirm="¿Confirmas el cambio de estado de este horario?" size="sm" :variant="$horario['estado'] ? 'danger' : 'primary'">{{ $horario['estado'] ? 'Dar de baja' : 'Reactivar' }}</flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row><flux:table.cell colspan="6">Aún no hay horarios registrados.</flux:table.cell></flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal wire:model="mostrarFormulario" class="md:w-[42rem]">
        <form wire:submit="guardar" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $codigo === null ? 'Registrar horario' : 'Editar horario' }}</flux:heading>
                <flux:text class="mt-2">Indica la ruta del tren, sus horarios y el precio del boleto.</flux:text>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <flux:select wire:model="origenCodigo" label="Estación de origen">
                    <flux:select.option value="">Selecciona una estación</flux:select.option>
                    @foreach ($estaciones as $valor => $etiqueta)
                        <flux:select.option :value="$valor" wire:key="origen-{{ $valor }}">{{ $etiqueta }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="destinoCodigo" label="Estación de destino">
                    <flux:select.option value="">Selecciona una estación</flux:select.option>
                    @foreach ($estaciones as $valor => $etiqueta)
                        <flux:select.option :value="$valor" wire:key="destino-{{ $valor }}">{{ $etiqueta }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="servicio" label="Servicio" placeholder="Vistadome" />
                <flux:input wire:model="precio" type="number" step="0.01" min="0" label="Precio del boleto (S/)" />
                <flux:input wire:model="horaSalida" type="time" label="Hora de salida" />
                <flux:input wire:model="horaLlegada" type="time" label="Hora de llegada" />
            </div>
            <div class="flex justify-end gap-3">
                <flux:modal.close><flux:button type="button" variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" wire:loading.attr="disabled" wire:target="guardar" variant="primary">Guardar horario</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
