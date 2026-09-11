<div class="mx-auto w-full max-w-sm py-6 sm:py-12">
    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg">Iniciar sesión</flux:heading>
            <flux:text class="mt-2">Ingresa con tu correo y tu contraseña.</flux:text>
        </div>

        @error('general')
            <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
        @enderror

        <form wire:submit="iniciarSesion" class="space-y-6">
            <flux:input
                wire:model="correo"
                type="email"
                label="Correo electrónico"
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

            <flux:checkbox wire:model="recordar" label="Recordarme" />

            <flux:button type="submit" variant="primary" class="w-full">Iniciar sesión</flux:button>
        </form>

        <flux:text class="text-center">
            ¿Aún no tienes cuenta?
            <flux:link :href="route('registro')" wire:navigate>Crea una</flux:link>
        </flux:text>
    </flux:card>
</div>
