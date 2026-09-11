<div class="space-y-8">
    <div>
        <flux:heading size="xl">Panel de administración</flux:heading>
        <flux:text class="mt-2">Gestiona los usuarios, los parámetros y la sincronización con PeruRail y SENAMHI.</flux:text>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-tarjeta-modulo
            icono="users"
            titulo="Usuarios"
            descripcion="Da de alta, da de baja y cambia el perfil de los usuarios."
            :href="route('administracion.usuarios')"
        />

        <x-tarjeta-modulo
            icono="adjustments-horizontal"
            titulo="Parámetros y categorías"
            descripcion="Configura la distancia máxima caminable, la frecuencia de sincronización y las categorías."
            :href="route('administracion.configuracion')"
        />

        <x-tarjeta-modulo
            icono="arrow-path"
            titulo="Sincronización"
            descripcion="Revisa la bitácora y ejecuta una sincronización manual."
            :href="route('administracion.sincronizacion')"
        />

        <x-tarjeta-modulo
            icono="chart-bar"
            titulo="Reporte de uso"
            descripcion="Consulta las estaciones y las categorías más buscadas."
        />
    </div>
</div>
