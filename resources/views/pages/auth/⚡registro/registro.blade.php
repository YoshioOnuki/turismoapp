<div class="mx-auto w-full max-w-sm py-6 sm:py-12">
    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg">Crear cuenta</flux:heading>
            <flux:text class="mt-2">Regístrate para descubrir rutas a pie desde las estaciones de tren.</flux:text>
        </div>

        @error('general')
            <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
        @enderror

        <form wire:submit="registrar" class="space-y-6">
            <flux:input
                wire:model="nombre"
                label="Nombre completo"
                autocomplete="name"
                required
                autofocus
            />

            <flux:input
                wire:model="correo"
                type="email"
                label="Correo electrónico"
                autocomplete="email"
                required
            />

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

            <flux:button type="submit" variant="primary" class="w-full">Crear cuenta</flux:button>
        </form>

        <flux:text class="text-center">
            ¿Ya tienes cuenta?
            <flux:link :href="route('login')" wire:navigate>Inicia sesión</flux:link>
        </flux:text>
    </flux:card>
</div>
