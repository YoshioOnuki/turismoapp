<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    @vite (['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    {{-- @fluxAppearance --}}
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-800 antialiased dark:bg-zinc-900 dark:text-zinc-100">
    <flux:header
        container
        sticky
        class="border-b border-zinc-200 bg-white/95 shadow-xs backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/95"
    >
        <flux:brand :href="route('inicio')" name="TurismoApp" wire:navigate.hover />

        @auth
            @php
                    $usuario = auth()->user();
                    $elementosNavegacion = match ($usuario->tipoPerfil()) {
                        \App\Enums\TipoPerfil::UsuarioFinal => [
                            ['etiqueta' => 'Mi panel', 'ruta' => 'turista.panel', 'activo' => 'turista.panel', 'icono' => 'rectangle-stack'],
                            ['etiqueta' => 'Planificar', 'ruta' => 'turista.planificar', 'activo' => 'turista.planificar', 'icono' => 'map'],
                            ['etiqueta' => 'Mis informes', 'ruta' => 'turista.informes', 'activo' => 'turista.informes*', 'icono' => 'document-text'],
                        ],
                        \App\Enums\TipoPerfil::TravelGroup => [
                            ['etiqueta' => 'Mi panel', 'ruta' => 'travel-group.panel', 'activo' => 'travel-group.panel', 'icono' => 'rectangle-stack'],
                            ['etiqueta' => 'Zonas', 'ruta' => 'travel-group.zonas', 'activo' => 'travel-group.zonas', 'icono' => 'map-pin'],
                            ['etiqueta' => 'Estaciones', 'ruta' => 'travel-group.estaciones', 'activo' => 'travel-group.estaciones', 'icono' => 'map'],
                            ['etiqueta' => 'Reporte', 'ruta' => 'travel-group.reporte-zonas', 'activo' => 'travel-group.reporte-zonas', 'icono' => 'document-text'],
                        ],
                        \App\Enums\TipoPerfil::AdministradorMtc => [
                            ['etiqueta' => 'Panel', 'ruta' => 'administracion.panel', 'activo' => 'administracion.panel', 'icono' => 'rectangle-stack'],
                            ['etiqueta' => 'Usuarios', 'ruta' => 'administracion.usuarios', 'activo' => 'administracion.usuarios', 'icono' => 'users'],
                            ['etiqueta' => 'Horarios', 'ruta' => 'administracion.horarios', 'activo' => 'administracion.horarios', 'icono' => 'clock'],
                            ['etiqueta' => 'Sincronización', 'ruta' => 'administracion.sincronizacion', 'activo' => 'administracion.sincronizacion', 'icono' => 'arrow-path'],
                            ['etiqueta' => 'Configuración', 'ruta' => 'administracion.configuracion', 'activo' => 'administracion.configuracion', 'icono' => 'adjustments-horizontal'],
                            ['etiqueta' => 'Reporte', 'ruta' => 'administracion.reporte-uso', 'activo' => 'administracion.reporte-uso', 'icono' => 'chart-bar'],
                        ],
                    };
                @endphp

            <flux:navbar class="ms-6 hidden xl:flex">
                @foreach ($elementosNavegacion as $elemento)
                    <flux:navbar.item
                        :href="route($elemento['ruta'])"
                        :current="request()->routeIs($elemento['activo'])"
                        wire:navigate.hover
                        wire:key="navegacion-{{ $elemento['ruta'] }}"
                    >
                        {{ $elemento['etiqueta'] }}
                    </flux:navbar.item>
                @endforeach
            </flux:navbar>

            <flux:spacer />

            <div class="hidden items-center gap-3 xl:flex">
                <div class="text-end">
                    <flux:text variant="strong" class="font-medium">{{ $usuario->usu_nombre }}</flux:text>
                    <flux:text size="sm">{{ $usuario->tipoPerfil()->etiqueta() }}</flux:text>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:button type="submit" variant="ghost" size="sm" icon="arrow-right-start-on-rectangle">
                        Cerrar sesión
                    </flux:button>
                </form>
            </div>

            <flux:dropdown position="bottom" align="end" class="xl:hidden">
                <flux:button variant="ghost" size="sm">Menú</flux:button>

                <flux:menu>
                    <div class="px-2 py-2">
                        <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $usuario->usu_nombre }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-300">{{ $usuario->tipoPerfil()->etiqueta() }}</p>
                    </div>
                    <flux:menu.separator />
                    @foreach ($elementosNavegacion as $elemento)
                        <flux:menu.item
                            :href="route($elemento['ruta'])"
                            :icon="$elemento['icono']"
                            wire:navigate.hover
                            wire:key="menu-{{ $elemento['ruta'] }}"
                        >
                            {{ $elemento['etiqueta'] }}
                        </flux:menu.item>
                    @endforeach
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:menu.item type="submit" icon="arrow-right-start-on-rectangle">
                            Cerrar sesión</flux:menu.item
                        >
                    </form>
                </flux:menu>
            </flux:dropdown>
        @else
            <flux:spacer />
            <flux:navbar>
                <flux:navbar.item :href="route('login')" wire:navigate.hover>Iniciar sesión</flux:navbar.item>
                <flux:navbar.item :href="route('registro')" wire:navigate.hover>Crear cuenta</flux:navbar.item>
            </flux:navbar>
        @endauth
    </flux:header>

    <flux:main container wire:transition.navigate="contenido"> {{ $slot }} </flux:main>

    @livewireScripts
    @fluxScripts
</body>
</html>
