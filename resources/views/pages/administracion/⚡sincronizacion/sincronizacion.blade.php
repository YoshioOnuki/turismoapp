
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:link :href="route('administracion.panel')" wire:navigate icon="arrow-left">
                Volver al panel
            </flux:link>

            <flux:heading size="xl" class="mt-4">Sincronización de datos</flux:heading>
            <flux:text class="mt-2">
                Actualiza las estaciones y horarios de PeruRail y los pronósticos de SENAMHI.
            </flux:text>
        </div>

        <flux:button
            wire:click="sincronizar"
            wire:loading.attr="disabled"
            wire:target="sincronizar"
            variant="primary"
            icon="arrow-path"
        >
            <span wire:loading.remove wire:target="sincronizar">Sincronizar ahora</span>
            <span wire:loading wire:target="sincronizar">Sincronizando...</span>
        </flux:button>
    </div>

    @if ($mensaje && $sincronizacionConIncidencias)
        <flux:callout variant="warning" icon="exclamation-triangle" :heading="$mensaje" />
    @elseif ($mensaje)
        <flux:callout variant="success" icon="check-circle" :heading="$mensaje" />
    @endif

    @error('general')
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
    @enderror

    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($fuentes as $codigo => $fuente)
            <flux:card class="space-y-5" wire:key="fuente-{{ $codigo }}">
                <div class="flex items-center justify-between gap-3">
                    <flux:heading size="lg">{{ $fuente['nombre'] }}</flux:heading>

                    @if ($fuente['resultado'] === 'exito')
                        <flux:badge color="green">Actualizada</flux:badge>
                    @elseif ($fuente['resultado'] === 'error')
                        <flux:badge color="red">Con incidencia</flux:badge>
                    @else
                        <flux:badge>Sin ejecuciones</flux:badge>
                    @endif
                </div>

                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Último intento</dt>
                        <dd class="mt-1">{{ $fuente['ultima_ejecucion'] ?? 'Aún no registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Última actualización exitosa</dt>
                        <dd class="mt-1">{{ $fuente['ultima_actualizacion'] ?? 'Aún no registrada' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Registros del último intento</dt>
                        <dd class="mt-1">{{ $fuente['registros'] }}</dd>
                    </div>
                </dl>

                @if ($fuente['resultado'] === 'error' && $fuente['ultima_actualizacion'])
                    <flux:text size="sm">
                        Se conservan los datos de la última actualización exitosa.
                    </flux:text>
                @endif
            </flux:card>
        @endforeach
    </div>

    <section class="space-y-4">
        <div>
            <flux:heading size="lg">Bitácora de sincronización</flux:heading>
            <flux:text class="mt-1">Últimas ejecuciones con su fecha, fuente, registros procesados y resultado.</flux:text>
        </div>

        <flux:card>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Fecha y hora</flux:table.column>
                    <flux:table.column>Fuente</flux:table.column>
                    <flux:table.column>Tipo</flux:table.column>
                    <flux:table.column align="end">Registros</flux:table.column>
                    <flux:table.column>Resultado</flux:table.column>
                    <flux:table.column>Detalle</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($bitacora as $registro)
                        <flux:table.row :key="'bitacora-'.$registro['codigo']">
                            <flux:table.cell>{{ $registro['fecha'] }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ $registro['fuente'] }}</flux:table.cell>
                            <flux:table.cell>{{ $registro['tipo'] }}</flux:table.cell>
                            <flux:table.cell align="end">{{ $registro['registros'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$registro['resultado'] === 'exito' ? 'green' : 'red'">
                                    {{ $registro['resultado'] === 'exito' ? 'Éxito' : 'Error' }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-normal">
                                <div class="flex max-w-md flex-col gap-1">
                                    <span>{{ $registro['mensaje'] ?? '—' }}</span>
                                    @if ($registro['usuario'])
                                        <span class="text-zinc-500 dark:text-zinc-400">Por {{ $registro['usuario'] }}</span>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6">Aún no hay sincronizaciones registradas.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </section>
</div>
