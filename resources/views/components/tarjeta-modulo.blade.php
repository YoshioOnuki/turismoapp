@props([
    'icono',
    'titulo',
    'descripcion',
    'href' => null,
])

<flux:card class="flex flex-col gap-3">
    <div class="flex items-start justify-between gap-3">
        <flux:icon :icon="$icono" class="size-6 text-accent-content" />

        @unless ($href)
            <flux:badge size="sm">Próximamente</flux:badge>
        @endunless
    </div>

    <flux:heading size="lg">{{ $titulo }}</flux:heading>

    <flux:text>{{ $descripcion }}</flux:text>

    @if ($href)
        <flux:link :href="$href" wire:navigate class="mt-auto">Entrar</flux:link>
    @endif
</flux:card>
