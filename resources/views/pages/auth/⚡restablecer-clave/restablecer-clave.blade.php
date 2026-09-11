
<div class="mx-auto w-full max-w-sm py-6 sm:py-12">
    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg">Restablecer contraseña</flux:heading>
            <flux:text class="mt-2">Elige una contraseña nueva para volver a ingresar a tu cuenta.</flux:text>
        </div>

        @error('general')
            <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
        @enderror

        <form wire:submit="restablecerClave" class="space-y-6">
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
                label="Contraseña nueva"
                description="Mínimo 8 caracteres."
                autocomplete="new-password"
                required
                viewable
            />

            <flux:input
                wire:model="clave_confirmation"
                type="password"
                label="Confirmar contraseña nueva"
                autocomplete="new-password"
                required
                viewable
            />

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                Restablecer contraseña
            </flux:button>
        </form>

        <flux:text class="text-center">
            <flux:link :href="route('login')" wire:navigate>Volver a iniciar sesión</flux:link>
        </flux:text>
    </flux:card>
</div>
