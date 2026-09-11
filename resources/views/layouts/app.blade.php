<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
        {{-- @fluxAppearance --}}
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-800 antialiased dark:bg-zinc-900 dark:text-zinc-100">
        <flux:header container class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:brand :href="route('inicio')" name="TurismoApp" wire:navigate />

            <flux:spacer />

            @auth
                @php($usuario = auth()->user())

                <flux:navbar class="me-3">
                    <flux:navbar.item :href="route($usuario->tipoPerfil()->rutaPanel())" wire:navigate>Mi panel</flux:navbar.item>
                </flux:navbar>

                <div class="flex items-center gap-3">
                    <div class="hidden text-end sm:block">
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
            @else
                <flux:navbar>
                    <flux:navbar.item :href="route('login')" wire:navigate>Iniciar sesión</flux:navbar.item>
                    <flux:navbar.item :href="route('registro')" wire:navigate>Crear cuenta</flux:navbar.item>
                </flux:navbar>
            @endauth
        </flux:header>

        <flux:main container>
            {{ $slot }}
        </flux:main>

        @livewireScripts
        @fluxScripts
    </body>
</html>
