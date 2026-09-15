<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:link :href="route('administracion.panel')" wire:navigate icon="arrow-left">Volver al panel</flux:link>
            <flux:heading size="xl" class="mt-4">Gestión de usuarios</flux:heading>
            <flux:text class="mt-2">Crea cuentas, asigna perfiles y administra su acceso.</flux:text>
        </div>
        <flux:button wire:click="nuevo" variant="primary" icon="user-plus">Nuevo usuario</flux:button>
    </div>

    @if ($mensaje)
        <flux:callout variant="success" icon="check-circle" :heading="$mensaje" />
    @endif
    @error('general')
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="$message" />
    @enderror

    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Usuario</flux:table.column>
                <flux:table.column>Perfil</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($usuarios as $usuario)
                    <flux:table.row :key="$usuario['codigo']">
                        <flux:table.cell variant="strong">
                            <div class="flex flex-col gap-1">
                                <span>{{ $usuario['nombre'] }}</span>
                                <span class="font-normal text-zinc-500 dark:text-zinc-400">{{ $usuario['correo'] }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:select size="sm" :value="$usuario['perfil']" wire:change="cambiarPerfil({{ $usuario['codigo'] }}, $event.target.value)" :disabled="$usuario['codigo'] === auth()->id()">
                                @foreach (\App\Enums\TipoPerfil::cases() as $tipoPerfil)
                                    <flux:select.option :value="$tipoPerfil->value" :selected="$tipoPerfil->value === $usuario['perfil']">{{ $tipoPerfil->etiqueta() }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$usuario['estado'] ? 'green' : 'zinc'">{{ $usuario['estado'] ? 'Activo' : 'Inactivo' }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button wire:click="cambiarEstado({{ $usuario['codigo'] }})" wire:confirm="¿Confirmas el cambio de estado?" size="sm" :variant="$usuario['estado'] ? 'danger' : 'primary'" :disabled="$usuario['codigo'] === auth()->id()">
                                {{ $usuario['estado'] ? 'Dar de baja' : 'Reactivar' }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal wire:model="mostrarFormulario" class="md:w-[42rem]">
        <form wire:submit="crear" class="space-y-6">
            <div>
                <flux:heading size="lg">Registrar usuario</flux:heading>
                <flux:text class="mt-2">Completa los datos y asigna el perfil inicial.</flux:text>
            </div>
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
            <div class="flex justify-end gap-3">
                <flux:modal.close><flux:button type="button" variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" wire:loading.attr="disabled" wire:target="crear" variant="primary">Crear usuario</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
