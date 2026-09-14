<div class="mx-auto max-w-6xl space-y-7 pb-12">
    @unless ($configuracionInicial)
        <flux:link :href="route('turista.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
    @endunless

    <section
        class="relative isolate overflow-hidden rounded-3xl border border-sky-200 bg-linear-to-br from-sky-50 via-white to-emerald-50 px-6 py-8 shadow-sm sm:px-9 sm:py-10 dark:border-sky-900/70 dark:from-sky-950/70 dark:via-zinc-900 dark:to-emerald-950/50"
    >
        <div
            class="absolute -end-20 -top-24 size-64 rounded-full bg-sky-300/30 blur-3xl dark:bg-sky-500/10"
            aria-hidden="true"
        ></div>
        <div
            class="absolute -start-20 -bottom-28 size-60 rounded-full bg-emerald-300/25 blur-3xl dark:bg-emerald-500/10"
            aria-hidden="true"
        ></div>

        <div class="relative grid items-center gap-8 lg:grid-cols-[1fr_auto]">
            <div class="max-w-3xl space-y-4">
                <flux:badge color="blue" icon="sparkles">
                    {{ $configuracionInicial ? 'Paso 1 de 2 · Personaliza tu experiencia' : 'Personaliza tus recomendaciones' }}
                </flux:badge>

                <div class="space-y-3">
                    <flux:heading level="1" class="text-3xl! font-semibold tracking-tight sm:text-4xl!">
                        {{ $configuracionInicial ? '¿Qué te gustaría descubrir?' : 'Actualiza tus intereses' }}
                    </flux:heading>
                    <flux:text class="max-w-2xl text-base! leading-7!">
                        Marca una o más categorías. Usaremos esta selección para mostrarte zonas turísticas que
                        realmente coincidan contigo al planificar cada visita.
                    </flux:text>
                </div>
            </div>

            <div
                class="hidden items-center gap-3 rounded-2xl border border-white/70 bg-white/70 p-4 shadow-sm backdrop-blur sm:flex dark:border-white/10 dark:bg-zinc-900/60"
            >
                <div class="grid size-10 place-items-center rounded-full bg-blue-600 font-semibold text-white">1</div>
                <div class="h-px w-10 bg-zinc-300 dark:bg-zinc-600"></div>
                <div
                    class="grid size-10 place-items-center rounded-full bg-zinc-200 font-semibold text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300"
                >
                    2
                </div>
                <div class="ms-1">
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">Tus intereses</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Luego podrás planificar</p>
                </div>
            </div>
        </div>
    </section>

    @error ('general')
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
    @enderror

    <form wire:submit="guardar" class="space-y-6">
        <section class="space-y-4" aria-labelledby="categorias-preferidas">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <flux:heading id="categorias-preferidas" size="lg">Elige tus categorías favoritas</flux:heading>
                    <flux:text class="mt-1">Puedes cambiar esta selección cuando quieras.</flux:text>
                </div>
                <flux:badge color="zinc" icon="check-circle">
                    {{ count($categoriasSeleccionadas) }} {{ count($categoriasSeleccionadas) === 1 ? 'seleccionada' : 'seleccionadas' }}
                </flux:badge>
            </div>

            @error ('categoriasSeleccionadas')
                <flux:callout variant="warning" icon="exclamation-triangle" :heading="$message" />
            @enderror

            @if ($categorias !== [])
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($categorias as $categoria)
                        <label
                            wire:key="categoria-preferencia-{{ $categoria['codigo'] }}"
                            class="group relative cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                wire:model.live="categoriasSeleccionadas"
                                value="{{ $categoria['codigo'] }}"
                                class="peer sr-only"
                            />
                            <span
                                class="flex h-full min-h-40 gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-md focus-within:ring-2 focus-within:ring-blue-500/40 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600 {{ $categoria['clasesSeleccion'] }}"
                            >
                                <span
                                    class="grid size-12 shrink-0 place-items-center rounded-2xl {{ $categoria['clasesIcono'] }}"
                                >
                                    <flux:icon :icon="$categoria['icono']" class="size-6" />
                                </span>
                                <span class="min-w-0 flex-1 space-y-2">
                                    <span class="flex items-start justify-between gap-3">
                                        <span
                                            class="text-lg font-semibold text-zinc-950 dark:text-white"
                                            >{{ $categoria['nombre'] }}</span
                                        >
                                        <span
                                            class="grid size-6 shrink-0 place-items-center rounded-full border border-zinc-300 text-transparent transition group-has-checked:border-blue-600 group-has-checked:bg-blue-600 group-has-checked:text-white dark:border-zinc-600"
                                        >
                                            <flux:icon icon="check" class="size-4" />
                                        </span>
                                    </span>
                                    <span
                                        class="block text-sm leading-6 text-zinc-600 dark:text-zinc-300"
                                        >{{ $categoria['descripcion'] }}</span
                                    >
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @else
                <flux:card class="border-dashed py-10 text-center">
                    <div
                        class="mx-auto grid size-12 place-items-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300"
                    >
                        <flux:icon icon="sparkles" class="size-6" />
                    </div>
                    <flux:heading class="mt-4">No hay categorías disponibles</flux:heading>
                    <flux:text class="mt-1"
                        >Vuelve a intentarlo cuando el administrador haya habilitado categorías.</flux:text
                    >
                </flux:card>
            @endif
        </section>

        <div
            class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700 dark:bg-zinc-800"
        >
            <div class="flex items-start gap-3">
                <div
                    class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300"
                >
                    <flux:icon icon="map" class="size-5" />
                </div>
                <div>
                    <p class="font-medium text-zinc-950 dark:text-white">Siguiente paso: arma tu visita</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Después de guardar llegarás a tu panel para continuar.</p>
                </div>
            </div>

            <flux:button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="guardar"
                variant="primary"
                icon:trailing="arrow-right"
                :disabled="$categorias === []"
            >
                <span wire:loading.remove wire:target="guardar">
                    {{ $configuracionInicial ? 'Guardar y continuar' : 'Guardar cambios' }}
                </span>
                <span wire:loading wire:target="guardar">Guardando...</span>
            </flux:button>
        </div>
    </form>
</div>
