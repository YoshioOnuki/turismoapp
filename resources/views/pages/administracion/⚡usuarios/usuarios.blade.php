
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:link :href="route('administracion.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
            <flux:heading size="xl" class="mt-4">Gestión de usuarios</flux:heading>
            <flux:text class="mt-2">Crea cuentas, asigna perfiles y administra su acceso.</flux:text>
        </div>
        <flux:button wire:click="$toggle('mostrarFormulario')" variant="primary" icon="user-plus">Nuevo usuario</flux:button>
    </div>

    @if ($mensaje)
        <flux:callout variant="success" icon="check-circle" :heading="$mensaje" />
    @endif
    @error('general')
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
    @enderror

    @if ($mostrarFormulario)
        <flux:card>
            <form wire:submit="crear" class="space-y-5">
                <flux:heading size="lg">Registrar usuario</flux:heading>
                <div class="grid gap-5 md:grid-cols-2">
                    <flux:input wire:model="nombre" label="Nombre" />
                    <flux:input wire:model="correo" type="email" label="Correo electrónico" />
                    <flux:input wire:model="clave" type="password" label="Contraseña" viewable />
                    <flux:input wire:model="claveConfirmation" type="password" label="Confirmar contraseña" viewable />
                    <flux:select wire:model="perfil" label="Perfil">
                        <flux:select.option value="">Selecciona un perfil</flux:select.option>
                        @foreach (\App\Enums\TipoPerfil::cases() as $tipoPerfil)
                            <flux:select.option :value="$tipoPerfil->value">{{ $tipoPerfil->etiqueta() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">Crear usuario</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($usuarios as $usuario)
            <flux:card class="space-y-4" wire:key="usuario-{{ $usuario['codigo'] }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ $usuario['nombre'] }}</flux:heading>
                        <flux:text>{{ $usuario['correo'] }}</flux:text>
                    </div>
                    <flux:badge :color="$usuario['estado'] ? 'green' : 'zinc'">{{ $usuario['estado'] ? 'Activo' : 'Inactivo' }}</flux:badge>
                </div>
                <flux:select label="Perfil" :value="$usuario['perfil']" wire:change="cambiarPerfil({{ $usuario['codigo'] }}, $event.target.value)" :disabled="$usuario['codigo'] === auth()->id()">
                    @foreach (\App\Enums\TipoPerfil::cases() as $tipoPerfil)
                        <flux:select.option :value="$tipoPerfil->value">{{ $tipoPerfil->etiqueta() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <div class="flex justify-end">
                    <flux:button wire:click="cambiarEstado({{ $usuario['codigo'] }})" wire:confirm="¿Confirmas el cambio de estado?" size="sm" :variant="$usuario['estado'] ? 'danger' : 'primary'" :disabled="$usuario['codigo'] === auth()->id()">
                        {{ $usuario['estado'] ? 'Dar de baja' : 'Reactivar' }}
                    </flux:button>
                </div>
            </flux:card>
        @endforeach
    </div>
</div>
