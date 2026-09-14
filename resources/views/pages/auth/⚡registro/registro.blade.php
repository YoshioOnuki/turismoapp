<div class="mx-auto w-full max-w-6xl py-4 sm:py-10">
    <section
        class="grid overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-xl shadow-zinc-950/5 lg:grid-cols-[0.9fr_1.1fr] dark:border-zinc-700 dark:bg-zinc-800"
    >
        <div
            class="relative isolate overflow-hidden bg-linear-to-br from-emerald-700 via-teal-700 to-sky-800 p-7 text-white sm:p-10 lg:p-12"
        >
            <div class="absolute -end-20 -top-20 size-72 rounded-full bg-white/15 blur-3xl" aria-hidden="true"></div>
            <div
                class="absolute -start-16 -bottom-24 size-64 rounded-full bg-sky-300/20 blur-3xl"
                aria-hidden="true"
            ></div>

            <div class="relative flex h-full flex-col justify-between gap-10">
                <div class="space-y-5">
                    <flux:badge color="green" icon="sparkles">Comienza en pocos pasos</flux:badge>
                    <div class="space-y-3">
                        <flux:heading level="1" class="text-3xl! font-semibold text-white! sm:text-4xl!">
                            Crea una experiencia hecha para ti
                        </flux:heading>
                        <p class="max-w-xl text-base leading-7 text-emerald-50/90">Después del registro elegirás qué te interesa. Con eso prepararemos recomendaciones más útiles desde el primer momento.</p>
                    </div>
                </div>

                <ol class="space-y-3 text-sm">
                    <li class="flex items-center gap-3">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white/15 font-semibold"
                            >1</span
                        >
                        Crea tu cuenta
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white/15 font-semibold"
                            >2</span
                        >
                        Elige tus preferencias
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white/15 font-semibold"
                            >3</span
                        >
                        Planifica tu primera visita
                    </li>
                </ol>
            </div>
        </div>

        <div class="flex items-center p-6 sm:p-10 lg:p-12">
            <div class="mx-auto w-full max-w-lg space-y-7">
                <div class="space-y-2">
                    <flux:heading size="xl">Crear cuenta</flux:heading>
                    <flux:text>Completa tus datos para empezar a descubrir rutas a pie.</flux:text>
                </div>

                @error ('general')
                    <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
                @enderror

                <form wire:submit="registrar" class="space-y-5">
                    <flux:input
                        wire:model="nombre"
                        label="Nombre completo"
                        placeholder="¿Cómo te llamas?"
                        autocomplete="name"
                        required
                        autofocus
                    />

                    <flux:input
                        wire:model="correo"
                        type="email"
                        label="Correo electrónico"
                        placeholder="tu@correo.com"
                        autocomplete="email"
                        required
                    />

                    <div class="grid gap-5 sm:grid-cols-2">
                        <flux:input
                            wire:model="clave"
                            type="password"
                            label="Contraseña"
                            description="Mínimo 8 caracteres."
                            autocomplete="new-password"
                            required
                            viewable
                        />

                        <flux:input
                            wire:model="clave_confirmation"
                            type="password"
                            label="Confirmar contraseña"
                            autocomplete="new-password"
                            required
                            viewable
                        />
                    </div>

                    <flux:button
                        type="submit"
                        variant="primary"
                        class="w-full"
                        icon:trailing="arrow-right"
                        wire:loading.attr="disabled"
                        wire:target="registrar"
                    >
                        <span wire:loading.remove wire:target="registrar">Crear cuenta y continuar</span>
                        <span wire:loading wire:target="registrar">Creando tu cuenta...</span>
                    </flux:button>
                </form>

                <flux:text class="text-center">
                    ¿Ya tienes una cuenta?
                    <flux:link :href="route('login')" wire:navigate>Inicia sesión</flux:link>
                </flux:text>
            </div>
        </div>
    </section>
</div>
