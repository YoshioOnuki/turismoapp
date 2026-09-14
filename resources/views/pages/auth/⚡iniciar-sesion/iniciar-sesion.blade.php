<div class="mx-auto w-full max-w-6xl py-4 sm:py-10">
    <section
        class="grid overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-xl shadow-zinc-950/5 lg:grid-cols-[1.05fr_0.95fr] dark:border-zinc-700 dark:bg-zinc-800"
    >
        <div
            class="relative isolate overflow-hidden bg-linear-to-br from-blue-700 via-sky-700 to-emerald-700 p-7 text-white sm:p-10 lg:p-12"
        >
            <div class="absolute -end-20 -top-20 size-72 rounded-full bg-white/15 blur-3xl" aria-hidden="true"></div>
            <div
                class="absolute -start-16 -bottom-24 size-64 rounded-full bg-emerald-300/20 blur-3xl"
                aria-hidden="true"
            ></div>

            <div class="relative flex h-full flex-col justify-between gap-10">
                <div class="space-y-5">
                    <flux:badge color="blue" icon="map">Tu próxima experiencia empieza aquí</flux:badge>
                    <div class="space-y-3">
                        <flux:heading level="1" class="text-3xl! font-semibold text-white! sm:text-4xl!">
                            Vuelve a descubrir Cusco a tu manera
                        </flux:heading>
                        <p class="max-w-xl text-base leading-7 text-sky-50/90">Inicia sesión para elegir tus intereses, explorar rutas caminables y conservar cada informe de viaje.</p>
                    </div>
                </div>

                <ul class="grid gap-3 text-sm sm:grid-cols-3 lg:grid-cols-1">
                    <li
                        class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur"
                    >
                        <flux:icon icon="sparkles" class="size-5 text-emerald-200" />
                        Recomendaciones según tus gustos
                    </li>
                    <li
                        class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur"
                    >
                        <flux:icon icon="map" class="size-5 text-emerald-200" />
                        Recorridos de ida y vuelta
                    </li>
                    <li
                        class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur"
                    >
                        <flux:icon icon="document-text" class="size-5 text-emerald-200" />
                        Informes listos para consultar
                    </li>
                </ul>
            </div>
        </div>

        <div class="flex items-center p-6 sm:p-10 lg:p-12">
            <div class="mx-auto w-full max-w-md space-y-7">
                <div class="space-y-2">
                    <flux:heading size="xl">Iniciar sesión</flux:heading>
                    <flux:text>Ingresa a tu cuenta para continuar planificando.</flux:text>
                </div>

                @if (session('estado'))
                    <flux:callout variant="success" icon="check-circle" :heading="session('estado')" />
                @endif

                @error ('general')
                    <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
                @enderror

                <form wire:submit="iniciarSesion" class="space-y-5">
                    <flux:input
                        wire:model="correo"
                        type="email"
                        label="Correo electrónico"
                        placeholder="tu@correo.com"
                        autocomplete="email"
                        required
                        autofocus
                    />

                    <flux:input
                        wire:model="clave"
                        type="password"
                        label="Contraseña"
                        autocomplete="current-password"
                        required
                        viewable
                    />

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <flux:checkbox wire:model="recordar" label="Recordarme" />
                        <flux:link :href="route('password.request')" wire:navigate>¿Olvidaste tu contraseña?</flux:link>
                    </div>

                    <flux:button
                        type="submit"
                        variant="primary"
                        class="w-full"
                        icon:trailing="arrow-right"
                        wire:loading.attr="disabled"
                        wire:target="iniciarSesion"
                    >
                        <span wire:loading.remove wire:target="iniciarSesion">Entrar a TurismoApp</span>
                        <span wire:loading wire:target="iniciarSesion">Comprobando...</span>
                    </flux:button>
                </form>

                <div class="flex items-center gap-4">
                    <flux:separator class="flex-1" />
                    <span class="text-xs tracking-wide text-zinc-400 uppercase">¿Primera vez?</span>
                    <flux:separator class="flex-1" />
                </div>

                <flux:text class="text-center">
                    Crea tu cuenta y personaliza tus recomendaciones.
                    <flux:link :href="route('registro')" wire:navigate>Registrarme</flux:link>
                </flux:text>
            </div>
        </div>
    </section>
</div>
