<div class="space-y-12 pt-2 pb-12 sm:pt-6">
    <section
        class="relative isolate overflow-hidden rounded-3xl border border-sky-200 bg-linear-to-br from-sky-50 via-white to-emerald-50 px-6 py-9 shadow-sm sm:px-9 sm:py-12 dark:border-sky-900/70 dark:from-sky-950/60 dark:via-zinc-900 dark:to-emerald-950/40"
    >
        <div
            class="absolute -end-20 -top-24 size-72 rounded-full bg-sky-300/30 blur-3xl dark:bg-sky-500/10"
            aria-hidden="true"
        ></div>
        <div
            class="absolute start-1/4 -bottom-28 size-64 rounded-full bg-emerald-300/25 blur-3xl dark:bg-emerald-500/10"
            aria-hidden="true"
        ></div>

        <div class="relative grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-7">
                <flux:badge color="blue" icon="map">Asesoría turística peatonal</flux:badge>

                <div class="space-y-4">
                    <flux:heading level="1" class="text-4xl! font-semibold tracking-tight sm:text-5xl!">
                        Descubre a pie lo mejor de cada estación de tren
                    </flux:heading>
                    <flux:text class="max-w-3xl text-base! leading-7!">
                        Elige lo que te interesa y la estación donde inicias tu visita. Reunimos zonas turísticas,
                        recorridos de ida y vuelta, clima, horarios y precios en una experiencia fácil de consultar.
                    </flux:text>
                </div>

                <div class="flex flex-wrap gap-3">
                    @auth
                        <flux:button
                            variant="primary"
                            :href="route(auth()->user()->tipoPerfil()->rutaPanel())"
                            wire:navigate
                            icon:trailing="arrow-right"
                        >
                            Ir a mi panel
                        </flux:button>
                    @else
                        <flux:button
                            variant="primary"
                            :href="route('registro')"
                            wire:navigate
                            icon:trailing="arrow-right"
                        >
                            Crear mi cuenta
                        </flux:button>
                        <flux:button :href="route('login')" wire:navigate>Ya tengo cuenta</flux:button>
                    @endauth
                </div>

                <div class="flex flex-wrap gap-x-6 gap-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                    <span class="flex items-center gap-2"
                        ><flux:icon icon="check-circle" class="size-5 text-emerald-600" /> Preferencias personales</span
                    >
                    <span class="flex items-center gap-2"
                        ><flux:icon icon="check-circle" class="size-5 text-emerald-600" /> Mapa interactivo</span
                    >
                    <span class="flex items-center gap-2"
                        ><flux:icon icon="check-circle" class="size-5 text-emerald-600" /> Informe en PDF</span
                    >
                </div>
            </div>

            <flux:card
                class="relative space-y-6 border-white/80! bg-white/85! shadow-xl shadow-sky-950/10 backdrop-blur dark:border-white/10! dark:bg-zinc-900/80!"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <flux:text size="sm">Tu recorrido personalizado</flux:text>
                        <flux:heading size="lg" class="mt-1">Tu informe en tres pasos</flux:heading>
                    </div>
                    <div
                        class="grid size-11 place-items-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300"
                    >
                        <flux:icon icon="document-text" class="size-6" />
                    </div>
                </div>

                <ol class="space-y-5">
                    <li class="flex gap-4">
                        <span
                            class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-600 font-semibold text-white shadow-sm"
                            >1</span
                        >
                        <div>
                            <flux:heading>Marca lo que te interesa</flux:heading>
                            <flux:text>Naturaleza, historia, aventura o gastronomía.</flux:text>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span
                            class="grid size-9 shrink-0 place-items-center rounded-full bg-sky-600 font-semibold text-white shadow-sm"
                            >2</span
                        >
                        <div>
                            <flux:heading>Elige la estación</flux:heading>
                            <flux:text>Verás las zonas caminables en un mapa, con el clima y los trenes.</flux:text>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span
                            class="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-600 font-semibold text-white shadow-sm"
                            >3</span
                        >
                        <div>
                            <flux:heading>Guarda y revisa</flux:heading>
                            <flux:text
                                >Consulta el informe completo y abre una copia en PDF cuando la necesites.</flux:text
                            >
                        </div>
                    </li>
                </ol>
            </flux:card>
        </div>
    </section>

    <section class="space-y-5">
        <div class="max-w-2xl">
            <flux:heading size="lg">Información integrada de tres fuentes</flux:heading>
            <flux:text class="mt-1"
                >Los datos se actualizan de forma programada para ayudarte a planificar con información útil.</flux:text
            >
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <flux:card class="space-y-3 transition duration-200 hover:-translate-y-1 hover:shadow-md">
                <div
                    class="grid size-11 place-items-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300"
                >
                    <flux:icon icon="ticket" class="size-6" />
                </div>
                <flux:heading>PeruRail</flux:heading>
                <flux:text class="leading-6"
                    >Estaciones, horarios, tiempos de viaje y precios de los boletos.</flux:text
                >
            </flux:card>

            <flux:card class="space-y-3 transition duration-200 hover:-translate-y-1 hover:shadow-md">
                <div
                    class="grid size-11 place-items-center rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-900/60 dark:text-sky-300"
                >
                    <flux:icon icon="cloud" class="size-6" />
                </div>
                <flux:heading>SENAMHI</flux:heading>
                <flux:text class="leading-6">Pronóstico del clima para la zona de cada estación.</flux:text>
            </flux:card>

            <flux:card class="space-y-3 transition duration-200 hover:-translate-y-1 hover:shadow-md">
                <div
                    class="grid size-11 place-items-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300"
                >
                    <flux:icon icon="map-pin" class="size-6" />
                </div>
                <flux:heading>Travel Group Perú</flux:heading>
                <flux:text class="leading-6"
                    >Zonas turísticas registradas y actualizadas cerca de las estaciones.</flux:text
                >
            </flux:card>
        </div>
    </section>
</div>
