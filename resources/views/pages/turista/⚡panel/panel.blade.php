<div class="space-y-8 pb-12">
    @if (session('estado'))
        <flux:callout variant="success" icon="check-circle" :heading="session('estado')" />
    @endif

    <section
        class="relative isolate overflow-hidden rounded-3xl bg-linear-to-br from-blue-700 via-sky-700 to-emerald-700 px-6 py-8 text-white shadow-xl shadow-blue-950/10 sm:px-9 sm:py-10"
    >
        <div class="absolute -end-16 -top-20 size-64 rounded-full bg-white/15 blur-3xl" aria-hidden="true"></div>
        <div
            class="absolute start-1/3 -bottom-28 size-60 rounded-full bg-emerald-300/20 blur-3xl"
            aria-hidden="true"
        ></div>

        <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="max-w-3xl space-y-5">
                <flux:badge color="blue" icon="map">Tu espacio de viaje</flux:badge>
                <div class="space-y-3">
                    <flux:heading level="1" class="text-3xl! font-semibold text-white! sm:text-4xl!">
                        Hola, {{ auth()->user()->usu_nombre }}
                    </flux:heading>
                    <p class="max-w-2xl text-base leading-7 text-sky-50/90">Descubre lugares que combinan con tus intereses y organiza una visita completa desde la estación de tren.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <flux:button :href="route('turista.planificar')" wire:navigate variant="primary" icon="map">
                        Planificar una visita
                    </flux:button>
                    <flux:button
                        :href="route('turista.informes')"
                        wire:navigate
                        class="border-white/30! bg-white/10! text-white! hover:bg-white/20!"
                    >
                        Ver mis informes
                    </flux:button>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-3">
                <div class="min-w-32 rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <dt class="text-xs tracking-wide text-sky-100 uppercase">Intereses</dt>
                    <dd class="mt-1 text-2xl font-semibold">{{ $preferenciasTotal }}</dd>
                </div>
                <div class="min-w-32 rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <dt class="text-xs tracking-wide text-sky-100 uppercase">Informes</dt>
                    <dd class="mt-1 text-2xl font-semibold">{{ $informesTotal }}</dd>
                </div>
            </dl>
        </div>
    </section>

    @if ($preferenciasTotal === 0)
        <flux:callout
            variant="warning"
            icon="sparkles"
            heading="Completa tus preferencias para recibir mejores recomendaciones."
        >
            <x-slot name="actions">
                <flux:button :href="route('turista.preferencias')" wire:navigate size="sm"
                    >Elegir preferencias</flux:button
                >
            </x-slot>
        </flux:callout>
    @endif

    <section class="space-y-4" aria-labelledby="acciones-turista">
        <div>
            <flux:heading id="acciones-turista" size="lg">¿Qué quieres hacer hoy?</flux:heading>
            <flux:text class="mt-1">Todo lo necesario para preparar y volver a consultar tus visitas.</flux:text>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-tarjeta-modulo
                icono="heart"
                tono="rose"
                titulo="Mis preferencias"
                descripcion="Ajusta las categorías que usamos para recomendarte experiencias y lugares."
                :href="route('turista.preferencias')"
                :etiqueta="$preferenciasTotal.' elegidas'"
            />

            <x-tarjeta-modulo
                icono="map"
                tono="emerald"
                titulo="Planificar mi visita"
                descripcion="Elige una estación y descubre rutas a pie, clima, horarios y precios."
                :href="route('turista.planificar')"
                etiqueta="Recomendado"
            />

            <x-tarjeta-modulo
                icono="document-text"
                tono="sky"
                titulo="Mis informes"
                descripcion="Revisa el detalle de tus visitas guardadas y abre una copia en PDF."
                :href="route('turista.informes')"
                :etiqueta="$informesTotal.' guardados'"
            />
        </div>
    </section>
</div>
