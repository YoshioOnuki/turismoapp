<div class="space-y-8">
    <div>
        <flux:link :href="route('travel-group.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
        <flux:heading size="xl" class="mt-4">Zonas por estación</flux:heading>
        <flux:text class="mt-2">Resumen de las zonas turísticas activas asignadas a cada estación.</flux:text>
    </div>

    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Estación</flux:table.column>
                <flux:table.column>Código externo</flux:table.column>
                <flux:table.column>Zonas activas</flux:table.column>
                <flux:table.column align="end">Total</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($estaciones as $estacion)
                    <flux:table.row :key="$estacion['codigo']">
                        <flux:table.cell variant="strong">{{ $estacion['estacion'] }}</flux:table.cell>
                        <flux:table.cell>{{ $estacion['codigo_externo'] }}</flux:table.cell>
                        <flux:table.cell class="whitespace-normal">{{ $estacion['zonas'] ?: 'Sin zonas asignadas' }}</flux:table.cell>
                        <flux:table.cell align="end">{{ $estacion['total'] }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
