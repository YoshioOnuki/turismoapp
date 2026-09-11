<div class="space-y-8">
    <div>
        <flux:link :href="route('travel-group.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
        <flux:heading size="xl" class="mt-4">Estaciones ferroviarias</flux:heading>
        <flux:text class="mt-2">Datos sincronizados desde PeruRail disponibles en modo de solo lectura.</flux:text>
    </div>

    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Estación</flux:table.column>
                <flux:table.column>Código externo</flux:table.column>
                <flux:table.column>Coordenadas</flux:table.column>
                <flux:table.column>Zonas activas</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($estaciones as $estacion)
                    <flux:table.row :key="$estacion['codigo']">
                        <flux:table.cell variant="strong">{{ $estacion['nombre'] }}</flux:table.cell>
                        <flux:table.cell>{{ $estacion['codigo_externo'] }}</flux:table.cell>
                        <flux:table.cell>{{ $estacion['latitud'] }}, {{ $estacion['longitud'] }}</flux:table.cell>
                        <flux:table.cell>{{ $estacion['zonas_activas'] }}</flux:table.cell>
                        <flux:table.cell><flux:badge :color="$estacion['estado'] ? 'green' : 'zinc'">{{ $estacion['estado'] ? 'Activa' : 'Inactiva' }}</flux:badge></flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row><flux:table.cell colspan="5">Aún no hay estaciones sincronizadas.</flux:table.cell></flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
