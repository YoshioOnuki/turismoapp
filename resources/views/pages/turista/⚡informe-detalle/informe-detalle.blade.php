<div class="space-y-8 pb-12">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <flux:link :href="route('turista.informes')" wire:navigate icon="arrow-left">Volver a mis informes</flux:link>

        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('turista.planificar')" wire:navigate size="sm" icon="map"
                >Nueva planificación</flux:button
            >
            <flux:button
                :href="route('turista.informes.pdf', $informeCodigo)"
                variant="primary"
                size="sm"
                icon="arrow-top-right-on-square"
                target="_blank"
                rel="noopener"
                >Abrir PDF</flux:button
            >
        </div>
    </div>

    <section
        class="relative overflow-hidden rounded-3xl border border-sky-200 bg-linear-to-br from-sky-50 via-white to-emerald-50 p-6 shadow-sm sm:p-8 dark:border-sky-900/70 dark:from-sky-950/60 dark:via-zinc-900 dark:to-emerald-950/40"
    >
        <div
            class="absolute -end-16 -top-20 size-56 rounded-full bg-sky-200/40 blur-3xl dark:bg-sky-500/10"
            aria-hidden="true"
        ></div>
        <div class="relative max-w-4xl space-y-5">
            <div class="flex flex-wrap items-center gap-2">
                <flux:badge color="blue" icon="document-text">Informe #{{ $informeCodigo }}</flux:badge>
                <flux:badge color="green" icon="map-pin">Ruta peatonal de ida y vuelta</flux:badge>
            </div>

            <div class="space-y-3">
                <flux:heading level="1" class="text-3xl! font-semibold tracking-tight sm:text-4xl!"
                    >Tu visita desde {{ $estacion }}</flux:heading
                >
                <flux:text class="max-w-3xl text-base! leading-7!">
                    Este informe reúne las zonas que coinciden con tus intereses, el recorrido completo desde la
                    estación, las opciones de tren y el pronóstico disponible al momento de guardarlo.
                </flux:text>
            </div>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                <span>Generado el {{ $fecha }}</span>
                @forelse ($categorias as $categoria)
                    <flux:badge wire:key="categoria-informe-{{ $loop->index }}" size="sm">{{ $categoria }}</flux:badge>
                @empty
                    <span>Sin preferencias registradas</span>
                @endforelse
            </div>
        </div>
    </section>

    <section aria-labelledby="resumen-informe">
        <flux:heading id="resumen-informe" size="lg" class="sr-only">Resumen del informe</flux:heading>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <flux:card class="space-y-2">
                <flux:text size="sm">Opciones turísticas</flux:text>
                <p class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $resumen['zonas'] }}</p>
                <flux:text size="sm">zonas que coinciden contigo</flux:text>
            </flux:card>
            <flux:card class="space-y-2">
                <flux:text size="sm">Caminata más corta</flux:text>
                <p class="text-2xl font-semibold text-zinc-950 dark:text-white">
                    {{ $resumen['tiempo_minimo'] === null ? 'Sin datos' : $resumen['tiempo_minimo'].' min' }}
                </p>
                <flux:text size="sm">incluye ida y regreso</flux:text>
            </flux:card>
            <flux:card class="space-y-2">
                <flux:text size="sm">Recorrido más corto</flux:text>
                <p class="text-2xl font-semibold text-zinc-950 dark:text-white">
                    {{ $resumen['distancia_minima'] === null ? 'Sin datos' : number_format($resumen['distancia_minima'] / 1000, 1, ',', ' ').' km' }}
                </p>
                <flux:text size="sm">distancia total caminando</flux:text>
            </flux:card>
            <flux:card class="space-y-2">
                <flux:text size="sm">Pasaje desde</flux:text>
                <p class="text-2xl font-semibold text-zinc-950 dark:text-white">
                    {{ $resumen['precio_desde'] === null ? 'Sin datos' : 'S/ '.number_format($resumen['precio_desde'], 2) }}
                </p>
                <flux:text size="sm">según los trenes disponibles</flux:text>
            </flux:card>
        </div>
    </section>

    @if ($zonas !== [])
        <section
            wire:key="mapa-informe-{{ $informeCodigo }}"
            x-data="mapaPlanificacion(@js($estacionMapa), @js($zonas))"
            class="space-y-4"
            aria-labelledby="mapa-informe"
        >
            <div class="flex flex-col gap-1">
                <flux:heading id="mapa-informe" size="lg">Explora las opciones en el mapa</flux:heading>
                <flux:text
                    >La estación es el punto de salida y regreso. Cada línea muestra la conexión orientativa con una
                    zona, no una navegación GPS paso a paso.</flux:text
                >
            </div>

            <flux:card class="space-y-4 overflow-hidden">
                <div class="max-w-md">
                    <flux:select x-model="zonaSeleccionada" x-on:change="seleccionarZona()" label="Centrar el mapa en">
                        <flux:select.option value="">Todas las zonas</flux:select.option>
                        @foreach ($zonas as $indice => $zona)
                            <flux:select.option
                                :value="$zona['codigo']"
                                wire:key="opcion-detalle-{{ $zona['codigo'] }}"
                            >
                                {{ $indice + 1 }}. {{ $zona['nombre'] }}</flux:select.option
                            >
                        @endforeach
                    </flux:select>
                </div>
                <p x-show="cargando" role="status" class="text-sm text-zinc-600 dark:text-zinc-300">Cargando mapa...</p>
                <p
                    x-cloak
                    x-show="aviso"
                    x-text="aviso"
                    role="status"
                    class="text-sm text-amber-700 dark:text-amber-300"
                ></p>
                <div
                    wire:ignore
                    x-ref="mapa"
                    class="relative isolate h-80 rounded-2xl sm:h-[28rem]"
                    aria-label="Mapa de la estación y las zonas incluidas en el informe"
                ></div>
            </flux:card>
        </section>
    @endif

    <section class="space-y-4" aria-labelledby="zonas-informe">
        <div>
            <flux:heading id="zonas-informe" size="lg">Zonas recomendadas</flux:heading>
            <flux:text class="mt-1"
                >Cada alternativa parte de {{ $estacion }} y termina nuevamente en la estación.</flux:text
            >
        </div>

        @if ($zonas === [])
            <flux:callout icon="information-circle" heading="Este informe no contiene zonas turísticas.">
                <flux:callout.text>
                    Prueba una nueva planificación con más categorías o una estación diferente.</flux:callout.text
                >
                <x-slot name="actions">
                    <flux:button :href="route('turista.planificar')" wire:navigate size="sm"
                        >Planificar otra visita</flux:button
                    >
                </x-slot>
            </flux:callout>
        @else
            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($zonas as $indice => $zona)
                    @php
                        $dificultad = ucfirst((string) ($zona['dificultad'] ?? 'no indicada'));
                        $colorDificultad = match (strtolower((string) ($zona['dificultad'] ?? ''))) {
                            'baja' => 'green',
                            'media' => 'amber',
                            'alta' => 'red',
                            default => 'zinc',
                        };
                    @endphp
                    <article
                        wire:key="zona-informe-{{ $zona['codigo'] ?? $indice }}"
                        class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
                    >
                        @if ($zona['imagen'] ?? null)
                            <img
                                src="{{ $zona['imagen'] }}"
                                alt="Vista de {{ $zona['nombre'] ?? 'la zona turística' }}"
                                class="h-48 w-full object-cover"
                            />
                        @else
                            <div
                                class="grid h-32 place-items-center bg-linear-to-br from-sky-100 to-emerald-100 dark:from-sky-950 dark:to-emerald-950"
                            >
                                <div
                                    class="grid size-14 place-items-center rounded-full bg-white/80 text-sky-700 shadow-sm dark:bg-zinc-900/70 dark:text-sky-300"
                                >
                                    <flux:icon icon="map-pin" class="size-7" />
                                </div>
                            </div>
                        @endif

                        <div class="space-y-5 p-5 sm:p-6">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:badge color="blue" size="sm">Opción {{ $indice + 1 }}</flux:badge>
                                    <flux:badge :color="$colorDificultad" size="sm"
                                        >Dificultad {{ $dificultad }}</flux:badge
                                    >
                                </div>
                                <flux:heading size="lg">{{ $zona['nombre'] ?? 'Zona turística' }}</flux:heading>
                                <flux:text>{{ $zona['descripcion'] ?? 'Sin descripción disponible.' }}</flux:text>
                            </div>

                            <dl
                                class="grid grid-cols-3 gap-3 border-t border-zinc-200 pt-4 text-sm dark:border-zinc-700"
                            >
                                <div class="space-y-1">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Categoría</dt>
                                    <dd class="font-medium text-zinc-900 dark:text-white">
                                        {{ $zona['categoria'] ?? 'No indicada' }}
                                    </dd>
                                </div>
                                <div class="space-y-1">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Ida y vuelta</dt>
                                    <dd class="font-medium text-zinc-900 dark:text-white">
                                        {{ number_format((float) ($zona['distancia_total'] ?? 0) / 1000, 1, ',', ' ') }} km
                                    </dd>
                                </div>
                                <div class="space-y-1">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Tiempo</dt>
                                    <dd class="font-medium text-zinc-900 dark:text-white">
                                        {{ $zona['tiempo_minutos'] ?? 0 }} min aprox.
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <div class="grid gap-6 xl:grid-cols-5">
        <section class="space-y-4 xl:col-span-3" aria-labelledby="trenes-informe">
            <div>
                <flux:heading id="trenes-informe" size="lg">Cómo llegar en tren</flux:heading>
                <flux:text class="mt-1">
                    Trenes registrados con llegada a {{ $estacion }}.
                    @if ($actualizacion['trenes'])
                        Datos actualizados al {{ $actualizacion['trenes'] }}.
                    @endif
                </flux:text>
            </div>

            <div class="space-y-3">
                @forelse ($trenes as $indice => $tren)
                    <flux:card wire:key="tren-informe-{{ $tren['codigo'] ?? $indice }}" class="space-y-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <flux:heading>{{ $tren['servicio'] ?? 'Servicio ferroviario' }}</flux:heading>
                                <flux:text size="sm">Desde {{ $tren['origen'] ?? 'origen no indicado' }}</flux:text>
                            </div>
                            <p class="text-lg font-semibold text-zinc-950 dark:text-white">S/ {{ number_format((float) ($tren['precio'] ?? 0), 2) }}</p>
                        </div>

                        <div class="flex items-center gap-4 rounded-xl bg-zinc-50 px-4 py-3 dark:bg-zinc-900/70">
                            <div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Salida</p>
                                <p class="font-semibold">{{ $tren['salida'] ?? '--:--' }}</p>
                            </div>
                            <div class="h-px flex-1 bg-zinc-300 dark:bg-zinc-600" aria-hidden="true"></div>
                            <div class="text-center">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Viaje</p>
                                <p class="text-sm font-medium">{{ $tren['duracion'] ?? 'No indicado' }}</p>
                            </div>
                            <div class="h-px flex-1 bg-zinc-300 dark:bg-zinc-600" aria-hidden="true"></div>
                            <div class="text-end">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Llegada</p>
                                <p class="font-semibold">{{ $tren['llegada'] ?? '--:--' }}</p>
                            </div>
                        </div>
                    </flux:card>
                @empty
                    <flux:card
                        ><flux:text
                            >No había trenes de llegada registrados cuando se generó este informe.</flux:text
                        ></flux:card
                    >
                @endforelse
            </div>
        </section>

        <section class="space-y-4 xl:col-span-2" aria-labelledby="clima-informe">
            <div>
                <flux:heading id="clima-informe" size="lg">Clima para tu visita</flux:heading>
                <flux:text class="mt-1">
                    Pronóstico asociado a la estación.
                    @if ($actualizacion['clima'])
                        Actualizado al {{ $actualizacion['clima'] }}.
                    @endif
                </flux:text>
            </div>

            @if (! $climaVigente && $clima !== [])
                <flux:callout variant="warning" icon="exclamation-triangle" heading="Pronóstico no vigente">
                    <flux:callout.text>
                        El SENAMHI no entregó datos vigentes; este es el último pronóstico que estaba
                        disponible.</flux:callout.text
                    >
                </flux:callout>
            @endif

            <div class="space-y-3">
                @forelse ($clima as $indice => $dia)
                    <flux:card wire:key="clima-informe-{{ $dia['codigo'] ?? $indice }}">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="grid size-10 shrink-0 place-items-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300"
                                >
                                    <flux:icon icon="cloud" class="size-5" />
                                </div>
                                <div>
                                    <flux:heading>{{ $dia['fecha'] ?? 'Fecha no indicada' }}</flux:heading>
                                    <flux:text
                                        size="sm"
                                        >{{ $dia['descripcion'] ?? 'Condición no indicada' }}</flux:text
                                    >
                                </div>
                            </div>
                            <div class="text-end">
                                <p class="font-semibold text-zinc-950 dark:text-white">{{ $dia['minima'] ?? '--' }}° - {{ $dia['maxima'] ?? '--' }}°</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $dia['lluvia'] ?? 0 }}% lluvia</p>
                            </div>
                        </div>
                    </flux:card>
                @empty
                    <flux:card
                        ><flux:text
                            >No había un pronóstico disponible cuando se generó este informe.</flux:text
                        ></flux:card
                    >
                @endforelse
            </div>
        </section>
    </div>

    <div
        class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700 dark:bg-zinc-800"
    >
        <div>
            <flux:heading>¿Quieres llevar este informe contigo?</flux:heading>
            <flux:text size="sm" class="mt-1"
                >Ábrelo en una pestaña nueva y descárgalo cuando estés listo desde el visor del navegador.</flux:text
            >
        </div>
        <flux:button
            :href="route('turista.informes.pdf', $informeCodigo)"
            variant="primary"
            icon="arrow-top-right-on-square"
            target="_blank"
            rel="noopener"
            >Abrir PDF</flux:button
        >
    </div>
</div>
