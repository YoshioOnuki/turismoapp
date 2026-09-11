<div class="space-y-8">
    <div>
        <flux:heading size="xl">Panel de Travel Group Perú</flux:heading>
        <flux:text class="mt-2">Mantén al día las zonas turísticas de cada estación.</flux:text>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-tarjeta-modulo
            icono="map-pin"
            titulo="Zonas turísticas"
            descripcion="Registra, edita y da de baja las zonas turísticas."
            :href="route('travel-group.zonas')"
        />

        <x-tarjeta-modulo
            icono="building-office-2"
            titulo="Estaciones"
            descripcion="Consulta las estaciones ferroviarias disponibles."
            :href="route('travel-group.estaciones')"
        />

        <x-tarjeta-modulo
            icono="clipboard-document-list"
            titulo="Zonas por estación"
            descripcion="Obtén el listado de estaciones con sus zonas asignadas."
            :href="route('travel-group.reporte-zonas')"
        />
    </div>
</div>
