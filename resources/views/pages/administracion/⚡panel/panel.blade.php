<div class="space-y-8 pb-12">
    <section
        class="relative isolate overflow-hidden rounded-3xl bg-linear-to-br from-zinc-950 via-slate-900 to-blue-950 px-6 py-8 text-white shadow-xl shadow-zinc-950/10 sm:px-9 sm:py-10"
    >
        <div class="absolute -end-16 -top-20 size-64 rounded-full bg-blue-500/20 blur-3xl" aria-hidden="true"></div>
        <div
            class="absolute start-1/4 -bottom-28 size-60 rounded-full bg-violet-500/15 blur-3xl"
            aria-hidden="true"
        ></div>

        <div class="relative grid gap-7 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="max-w-3xl space-y-4">
                <flux:badge color="blue" icon="adjustments-horizontal">Centro de control</flux:badge>
                <div class="space-y-3">
                    <flux:heading level="1" class="text-3xl! font-semibold text-white! sm:text-4xl!"
                        >Panel de administración</flux:heading
                    >
                    <p class="max-w-2xl text-base leading-7 text-zinc-200">Supervisa usuarios, datos ferroviarios, parámetros del sistema y sincronizaciones desde un solo lugar.</p>
                </div>
            </div>

            <flux:button
                :href="route('administracion.sincronizacion')"
                wire:navigate
                variant="primary"
                icon="arrow-path"
            >
                Revisar sincronización
            </flux:button>
        </div>
    </section>

    <section class="space-y-4" aria-labelledby="acciones-administracion">
        <div>
            <flux:heading id="acciones-administracion" size="lg">Administración del sistema</flux:heading>
            <flux:text class="mt-1">Accede rápidamente a cada área operativa de TurismoApp.</flux:text>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-tarjeta-modulo
                icono="users"
                tono="sky"
                titulo="Usuarios"
                descripcion="Da de alta, da de baja y administra los perfiles de acceso."
                :href="route('administracion.usuarios')"
            />

            <x-tarjeta-modulo
                icono="adjustments-horizontal"
                tono="violet"
                titulo="Parámetros y categorías"
                descripcion="Configura distancias, frecuencias y categorías para las recomendaciones."
                :href="route('administracion.configuracion')"
            />

            <x-tarjeta-modulo
                icono="arrow-path"
                tono="emerald"
                titulo="Sincronización"
                descripcion="Revisa la bitácora y actualiza los datos externos de forma controlada."
                :href="route('administracion.sincronizacion')"
                etiqueta="Monitoreo"
            />

            <x-tarjeta-modulo
                icono="clock"
                tono="amber"
                titulo="Horarios y precios"
                descripcion="Mantén los horarios, tiempos de viaje y precios de los trenes."
                :href="route('administracion.horarios')"
            />

            <x-tarjeta-modulo
                icono="chart-bar"
                tono="rose"
                titulo="Reporte de uso"
                descripcion="Consulta las estaciones y categorías con mayor actividad."
                :href="route('administracion.reporte-uso')"
            />
        </div>
    </section>
</div>
