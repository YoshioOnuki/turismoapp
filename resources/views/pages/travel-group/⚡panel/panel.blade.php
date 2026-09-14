<div class="space-y-8 pb-12">
    <section
        class="relative isolate overflow-hidden rounded-3xl border border-emerald-200 bg-linear-to-br from-emerald-50 via-white to-sky-50 px-6 py-8 shadow-sm sm:px-9 sm:py-10 dark:border-emerald-900/70 dark:from-emerald-950/60 dark:via-zinc-900 dark:to-sky-950/40"
    >
        <div
            class="absolute -end-20 -top-24 size-64 rounded-full bg-emerald-300/25 blur-3xl dark:bg-emerald-500/10"
            aria-hidden="true"
        ></div>

        <div class="relative grid gap-7 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="max-w-3xl space-y-4">
                <flux:badge color="green" icon="building-office-2">Gestión de destinos</flux:badge>
                <div class="space-y-3">
                    <flux:heading level="1" class="text-3xl! font-semibold sm:text-4xl!"
                        >Panel de Travel Group Perú</flux:heading
                    >
                    <flux:text class="max-w-2xl text-base! leading-7!">
                        Mantén actualizada la oferta turística alrededor de cada estación y revisa cómo se organiza para
                        los visitantes.
                    </flux:text>
                </div>
            </div>

            <flux:button :href="route('travel-group.zonas')" wire:navigate variant="primary" icon="plus">
                Gestionar zonas
            </flux:button>
        </div>
    </section>

    <section class="space-y-4" aria-labelledby="acciones-travel-group">
        <div>
            <flux:heading id="acciones-travel-group" size="lg">Gestión turística</flux:heading>
            <flux:text class="mt-1">Administra los lugares y consulta su cobertura desde las estaciones.</flux:text>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-tarjeta-modulo
                icono="map-pin"
                tono="emerald"
                titulo="Zonas turísticas"
                descripcion="Registra, edita y controla la disponibilidad de los lugares recomendados."
                :href="route('travel-group.zonas')"
                etiqueta="Principal"
            />

            <x-tarjeta-modulo
                icono="building-office-2"
                tono="sky"
                titulo="Estaciones"
                descripcion="Consulta las estaciones ferroviarias disponibles y su información principal."
                :href="route('travel-group.estaciones')"
            />

            <x-tarjeta-modulo
                icono="clipboard-document-list"
                tono="amber"
                titulo="Zonas por estación"
                descripcion="Revisa en un solo lugar las estaciones y las zonas turísticas vinculadas."
                :href="route('travel-group.reporte-zonas')"
            />
        </div>
    </section>
</div>
