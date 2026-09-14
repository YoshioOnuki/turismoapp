<div class="space-y-8">
    <div>
        <flux:link :href="route('turista.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
        <flux:heading size="xl" class="mt-4">Planificar mi visita</flux:heading>
        <flux:text class="mt-2"
            >1. Marca lo que te interesa · 2. Elige la estación y busca · 3. Guarda y revisa tu informe
            completo.</flux:text
        >
    </div>

    <flux:card>
        <form wire:submit="buscar" class="space-y-6">
            <flux:checkbox.group
                wire:model="categoriasSeleccionadas"
                label="¿Qué te interesa?"
                description="Guardamos tu selección como preferencias para tus próximas búsquedas."
            >
                <div class="flex flex-wrap gap-x-6 gap-y-3">
                    @forelse ($categorias as $categoria)
                        <flux:checkbox
                            :value="$categoria['codigo']"
                            :label="$categoria['nombre']"
                            wire:key="categoria-{{ $categoria['codigo'] }}"
                        />
                    @empty
                        <flux:text>No hay categorías activas disponibles.</flux:text>
                    @endforelse
                </div>
            </flux:checkbox.group>

            <div class="flex flex-col gap-4 md:flex-row md:items-end">
                <div class="flex-1">
                    <flux:select wire:model="estacionCodigo" label="Estación ferroviaria">
                        <flux:select.option value="">Selecciona una estación</flux:select.option>
                        @foreach ($estaciones as $codigo => $nombre)
                            <flux:select.option :value="$codigo" wire:key="estacion-{{ $codigo }}">
                                {{ $nombre }}</flux:select.option
                            >
                        @endforeach
                    </flux:select>
                </div>
                <flux:button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="buscar"
                    variant="primary"
                    icon="magnifying-glass"
                    >Buscar zonas</flux:button
                >
            </div>
        </form>
    </flux:card>

    @error ('general')
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
    @enderror

    @if ($busquedaRealizada && $zonas === [])
        <flux:callout
            variant="warning"
            icon="information-circle"
            heading="No encontramos zonas que coincidan con tus preferencias y la distancia máxima configurada."
        >
            <flux:callout.text>Amplía tus criterios: marca más categorías o elige otra estación.</flux:callout.text>
        </flux:callout>
    @elseif ($busquedaRealizada)
        <section
            wire:key="mapa-{{ md5(json_encode([$estacionConsultada, $zonas])) }}"
            x-data="mapaPlanificacion(@js($estacionConsultada), @js($zonas))"
            class="space-y-4"
            aria-label="Mapa de zonas turísticas"
        >
            <flux:heading size="lg">Mapa del recorrido</flux:heading>
            <flux:text
                >La estación es el punto de salida y regreso. Las líneas son conexiones orientativas entre ubicaciones;
                no representan senderos ni indicaciones de navegación. La distancia y el tiempo corresponden al
                recorrido registrado por Travel Group.</flux:text
            >
            <flux:card class="space-y-4">
                <flux:select x-model="zonaSeleccionada" x-on:change="seleccionarZona()" label="Ver zona en el mapa">
                    <flux:select.option value="">Todas las zonas</flux:select.option>
                    @foreach ($zonas as $indice => $zona)
                        <flux:select.option :value="$zona['codigo']" wire:key="opcion-mapa-{{ $zona['codigo'] }}">
                            {{ $indice + 1 }}. {{ $zona['nombre'] }}</flux:select.option
                        >
                    @endforeach
                </flux:select>
                <p x-show="cargando" role="status" class="text-sm text-zinc-600 dark:text-zinc-300">Cargando mapa…</p>
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
                    class="relative isolate h-80 rounded-lg sm:h-96"
                    aria-label="Estación y zonas disponibles. Usa los controles para acercar o alejar el mapa."
                ></div>
                <flux:text size="sm"
                    >E: {{ $estacionConsultada['nombre'] }} · Los números identifican las zonas en el orden de la
                    tabla.</flux:text
                >
            </flux:card>
        </section>

        <section class="space-y-4">
            <flux:heading size="lg">Zonas disponibles</flux:heading>
            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Zona</flux:table.column>
                        <flux:table.column>Categoría</flux:table.column>
                        <flux:table.column>Ruta ida y vuelta</flux:table.column>
                        <flux:table.column>Dificultad</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($zonas as $zona)
                            <flux:table.row :key="$zona['codigo']">
                                <flux:table.cell variant="strong">
                                    <div class="flex items-center gap-3">
                                        @if ($zona['imagen'])
                                            <img
                                                src="{{ $zona['imagen'] }}"
                                                alt=""
                                                class="size-10 rounded-lg object-cover"
                                            />
                                        @else
                                            <div
                                                class="grid size-10 place-items-center rounded-lg bg-zinc-100 dark:bg-zinc-800"
                                            >
                                                <flux:icon icon="map-pin" class="size-5" />
                                            </div>
                                        @endif
                                        <div class="flex max-w-md flex-col gap-1 whitespace-normal">
                                            <span>{{ $zona['nombre'] }}</span>
                                            <span
                                                class="font-normal text-zinc-500 dark:text-zinc-400"
                                                >{{ $zona['descripcion'] }}</span
                                            >
                                        </div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>{{ $zona['categoria'] }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex flex-col gap-1">
                                        <span>{{ $zona['distancia_total'] }} m</span>
                                        <span class="text-zinc-500 dark:text-zinc-400"
                                            >{{ $zona['tiempo_minutos'] }} min aprox.</span
                                        >
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>{{ ucfirst($zona['dificultad']) }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </section>
    @endif

    @if ($busquedaRealizada)
        <div class="grid gap-6 xl:grid-cols-2">
            <section class="space-y-4">
                <div>
                    <flux:heading size="lg">Trenes que llegan a la estación</flux:heading>
                    @if ($actualizacion['trenes'])
                        <flux:text size="sm" class="mt-1"
                            >Datos de PeruRail actualizados al {{ $actualizacion['trenes'] }}.</flux:text
                        >
                    @endif
                </div>
                <flux:card>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Origen</flux:table.column>
                            <flux:table.column>Servicio</flux:table.column>
                            <flux:table.column>Horario</flux:table.column>
                            <flux:table.column>Precio</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($trenes as $tren)
                                <flux:table.row :key="$tren['codigo']">
                                    <flux:table.cell variant="strong">{{ $tren['origen'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $tren['servicio'] }}</flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex flex-col gap-1">
                                            <span>{{ $tren['salida'] }} – {{ $tren['llegada'] }}</span>
                                            <span class="text-zinc-500 dark:text-zinc-400"
                                                >{{ $tren['duracion'] }} de viaje</span
                                            >
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>S/ {{ $tren['precio'] }}</flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4">
                                        No hay trenes de llegada disponibles.</flux:table.cell
                                    ></flux:table.row
                                >
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </flux:card>
            </section>

            <section class="space-y-4">
                <div>
                    <flux:heading size="lg">Pronóstico del clima</flux:heading>
                    @if ($actualizacion['clima'])
                        <flux:text size="sm" class="mt-1"
                            >Pronóstico del SENAMHI actualizado al {{ $actualizacion['clima'] }}.</flux:text
                        >
                    @endif
                </div>
                @if (! $climaVigente && $clima !== [])
                    <flux:callout
                        variant="warning"
                        icon="exclamation-triangle"
                        heading="No hay un pronóstico vigente para esta estación."
                    >
                        <flux:callout.text>
                            Mostramos el último pronóstico disponible del SENAMHI. Tenlo en cuenta al planificar tu
                            visita.</flux:callout.text
                        >
                    </flux:callout>
                @endif
                <flux:card>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Fecha</flux:table.column>
                            <flux:table.column>Condición</flux:table.column>
                            <flux:table.column>Temperatura</flux:table.column>
                            <flux:table.column>Lluvia</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($clima as $pronostico)
                                <flux:table.row :key="$pronostico['codigo']">
                                    <flux:table.cell variant="strong">{{ $pronostico['fecha'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $pronostico['descripcion'] }}</flux:table.cell>
                                    <flux:table.cell>
                                        {{ $pronostico['minima'] }}° – {{ $pronostico['maxima'] }}°</flux:table.cell
                                    >
                                    <flux:table.cell>{{ $pronostico['lluvia'] }}%</flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4">
                                        No hay pronóstico disponible.</flux:table.cell
                                    ></flux:table.row
                                >
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </flux:card>
            </section>
        </div>

        @if ($mensajeInforme)
            <flux:callout variant="success" icon="check-circle" :heading="$mensajeInforme">
                @if ($informeCodigo)
                    <x-slot name="actions">
                        <flux:button
                            :href="route('turista.informes.detalle', $informeCodigo)"
                            wire:navigate
                            variant="primary"
                            size="sm"
                            icon="document-text"
                            >Ver informe completo</flux:button
                        >
                        <flux:button
                            :href="route('turista.informes.pdf', $informeCodigo)"
                            size="sm"
                            icon="arrow-top-right-on-square"
                            target="_blank"
                            rel="noopener"
                            >Abrir PDF</flux:button
                        >
                    </x-slot>
                @endif
            </flux:callout>
        @endif

        <div class="flex justify-end">
            <flux:button
                wire:click="generarInforme"
                wire:loading.attr="disabled"
                wire:target="generarInforme"
                variant="primary"
                icon="document-plus"
            >
                Guardar informe
            </flux:button>
        </div>
    @endif
</div>
