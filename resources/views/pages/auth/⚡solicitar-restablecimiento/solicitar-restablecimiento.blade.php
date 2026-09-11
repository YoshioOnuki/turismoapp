
<div class="mx-auto w-full max-w-sm py-6 sm:py-12">
    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg">Recuperar contraseña</flux:heading>
            <flux:text class="mt-2">Ingresa tu correo y te enviaremos un enlace para crear una contraseña nueva.</flux:text>
        </div>

        @if ($enviado)
            <flux:callout
                variant="success"
                icon="check-circle"
                heading="Revisa tu correo"
            >
                Si existe una cuenta activa con ese correo, recibirás un enlace de recuperación.
            </flux:callout>
        @endif

        @error('general')
            <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
        @enderror

        <form wire:submit="solicitarRestablecimiento" class="space-y-6">
            <flux:input
                wire:model="correo"
                type="email"
                label="Correo electrónico"
                autocomplete="email"
                required
                autofocus
            />

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                Enviar enlace de recuperación
            </flux:button>
        </form>

        <flux:text class="text-center">
            <flux:link :href="route('login')" wire:navigate>Volver a iniciar sesión</flux:link>
        </flux:text>
    </flux:card>
</div>
