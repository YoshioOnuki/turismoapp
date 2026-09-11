
<div class="space-y-8">
    <div>
        <flux:link :href="route('turista.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
        <flux:heading size="xl" class="mt-4">Mis informes</flux:heading>
        <flux:text class="mt-2">Historial de las planificaciones que guardaste.</flux:text>
    </div>

    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Fecha</flux:table.column>
                <flux:table.column>Estación</flux:table.column>
                <flux:table.column>Preferencias</flux:table.column>
                <flux:table.column>Zonas</flux:table.column>
                <flux:table.column>Trenes</flux:table.column>
                <flux:table.column>Acciones</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($informes as $informe)
                    <flux:table.row :key="$informe['codigo']">
                        <flux:table.cell>{{ $informe['fecha'] }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ $informe['estacion'] }}</flux:table.cell>
                        <flux:table.cell>{{ $informe['categorias'] ?: 'Sin preferencias' }}</flux:table.cell>
                        <flux:table.cell>{{ $informe['zonas'] }}</flux:table.cell>
                        <flux:table.cell>{{ $informe['trenes'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button :href="route('turista.informes.html', $informe['codigo'])" size="sm" icon="arrow-down-tray">
                                HTML
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row><flux:table.cell colspan="6">Aún no guardaste ningún informe.</flux:table.cell></flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
