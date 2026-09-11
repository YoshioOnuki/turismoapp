<div class="space-y-8">
    <div>
        <flux:link :href="route('administracion.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
        <flux:heading size="xl" class="mt-4">Reporte de uso</flux:heading>
        <flux:text class="mt-2">Frecuencia de estaciones y categorías en los informes guardados.</flux:text>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="space-y-4">
            <flux:heading size="lg">Estaciones más consultadas</flux:heading>
            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Estación</flux:table.column>
                        <flux:table.column align="end">Consultas</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($estaciones as $estacion)
                            <flux:table.row :key="$estacion['codigo']">
                                <flux:table.cell variant="strong">{{ $estacion['nombre'] }}</flux:table.cell>
                                <flux:table.cell align="end">{{ $estacion['consultas'] }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row><flux:table.cell colspan="2">Aún no hay consultas registradas.</flux:table.cell></flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </section>

        <section class="space-y-4">
            <flux:heading size="lg">Categorías más consultadas</flux:heading>
            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Categoría</flux:table.column>
                        <flux:table.column align="end">Consultas</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($categorias as $categoria)
                            <flux:table.row :key="$categoria['codigo']">
                                <flux:table.cell variant="strong">{{ $categoria['nombre'] }}</flux:table.cell>
                                <flux:table.cell align="end">{{ $categoria['consultas'] }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row><flux:table.cell colspan="2">Aún no hay categorías consultadas.</flux:table.cell></flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </section>
    </div>
</div>
