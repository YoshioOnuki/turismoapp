
<div class="space-y-8">
    <div>
        <flux:link :href="route('travel-group.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
        <flux:heading size="xl" class="mt-4">Estaciones ferroviarias</flux:heading>
        <flux:text class="mt-2">Datos sincronizados desde PeruRail disponibles en modo de solo lectura.</flux:text>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($estaciones as $estacion)
            <flux:card class="space-y-4" wire:key="estacion-{{ $estacion['codigo'] }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ $estacion['nombre'] }}</flux:heading>
                        <flux:text size="sm">{{ $estacion['codigo_externo'] }}</flux:text>
                    </div>
                    <flux:badge :color="$estacion['estado'] ? 'green' : 'zinc'">
                        {{ $estacion['estado'] ? 'Activa' : 'Inactiva' }}
                    </flux:badge>
                </div>

                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Coordenadas</dt>
                        <dd class="mt-1">{{ $estacion['latitud'] }}, {{ $estacion['longitud'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Zonas activas asociadas</dt>
                        <dd class="mt-1">{{ $estacion['zonas_activas'] }}</dd>
                    </div>
                </dl>
            </flux:card>
        @empty
            <flux:callout icon="information-circle" heading="Aún no hay estaciones sincronizadas." />
        @endforelse
    </div>
</div>
