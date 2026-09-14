<div class="space-y-8 pb-10">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:link :href="route('turista.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
            <flux:heading size="xl" class="mt-4">Mis informes</flux:heading>
            <flux:text class="mt-2"
                >Abre el detalle completo de tus visitas guardadas o lleva una copia en PDF.</flux:text
            >
        </div>
        <flux:button :href="route('turista.planificar')" wire:navigate variant="primary" icon="map"
            >Planificar nueva visita</flux:button
        >
    </div>

    @if ($informes !== [])
        <div class="grid gap-5 lg:grid-cols-2">
            @foreach ($informes as $informe)
                <article wire:key="informe-{{ $informe['codigo'] }}">
                    <flux:card
                        class="flex h-full flex-col gap-5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-2">
                                <flux:badge color="blue" size="sm">Informe #{{ $informe['codigo'] }}</flux:badge>
                                <flux:heading size="lg">{{ $informe['estacion'] }}</flux:heading>
                                <flux:text size="sm">Guardado el {{ $informe['fecha'] }}</flux:text>
                            </div>
                            <div
                                class="grid size-11 shrink-0 place-items-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300"
                            >
                                <flux:icon icon="map-pin" class="size-5" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p class="text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400">Preferencias</p>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $informe['categorias'] ?: 'Sin preferencias registradas' }}</p>
                        </div>

                        <dl class="grid grid-cols-2 gap-3 rounded-xl bg-zinc-50 p-4 dark:bg-zinc-900/70">
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Zonas recomendadas</dt>
                                <dd class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">
                                    {{ $informe['zonas'] }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Opciones de tren</dt>
                                <dd class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">
                                    {{ $informe['trenes'] }}
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-auto flex flex-wrap gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                            <flux:button
                                :href="route('turista.informes.detalle', $informe['codigo'])"
                                wire:navigate
                                variant="primary"
                                size="sm"
                                icon="document-text"
                            >
                                Ver informe completo
                            </flux:button>
                            <flux:button
                                :href="route('turista.informes.pdf', $informe['codigo'])"
                                size="sm"
                                icon="arrow-top-right-on-square"
                                target="_blank"
                                rel="noopener"
                            >
                                Abrir PDF
                            </flux:button>
                        </div>
                    </flux:card>
                </article>
            @endforeach
        </div>
    @else
        <flux:card class="py-12 text-center">
            <div
                class="mx-auto grid size-14 place-items-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300"
            >
                <flux:icon icon="document-text" class="size-7" />
            </div>
            <flux:heading size="lg" class="mt-5">Todavía no tienes informes</flux:heading>
            <flux:text class="mx-auto mt-2 max-w-md"
                >Elige tus intereses y una estación para crear tu primera planificación turística.</flux:text
            >
            <flux:button :href="route('turista.planificar')" wire:navigate variant="primary" class="mt-5" icon="map"
                >Crear mi primer informe</flux:button
            >
        </flux:card>
    @endif
</div>
