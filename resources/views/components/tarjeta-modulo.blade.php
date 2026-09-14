@props ([
    'icono',
    'titulo',
    'descripcion',
    'href' => null,
    'tono' => 'sky',
    'etiqueta' => null,
])

@php
    [$clasesIcono, $clasesBarra] = match ($tono) {
        'emerald' => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300', 'from-emerald-500 to-teal-400'],
        'amber' => ['bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300', 'from-amber-500 to-orange-400'],
        'rose' => ['bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300', 'from-rose-500 to-pink-400'],
        'violet' => ['bg-violet-100 text-violet-700 dark:bg-violet-900/60 dark:text-violet-300', 'from-violet-500 to-fuchsia-400'],
        default => ['bg-sky-100 text-sky-700 dark:bg-sky-900/60 dark:text-sky-300', 'from-sky-500 to-blue-500'],
    };
@endphp

<flux:card
    class="group relative flex h-full min-h-56 flex-col gap-4 overflow-hidden transition duration-200 hover:-translate-y-1 hover:shadow-lg"
>
    <div class="absolute inset-x-0 top-0 h-1 bg-linear-to-r {{ $clasesBarra }}" aria-hidden="true"></div>

    <div class="flex items-start justify-between gap-3">
        <div
            class="grid size-12 place-items-center rounded-2xl {{ $clasesIcono }} transition duration-200 group-hover:scale-105"
        >
            <flux:icon :icon="$icono" class="size-6" />
        </div>

        @if ($etiqueta)
            <flux:badge size="sm">{{ $etiqueta }}</flux:badge>
        @elseif (! $href)
            <flux:badge size="sm">Próximamente</flux:badge>
        @endif
    </div>

    <div class="space-y-2">
        <flux:heading size="lg">{{ $titulo }}</flux:heading>
        <flux:text class="leading-6">{{ $descripcion }}</flux:text>
    </div>

    @if ($href)
        <div class="mt-auto flex items-center gap-2 text-sm font-medium text-blue-700 dark:text-blue-300">
            Abrir módulo
            <flux:icon icon="arrow-right" class="size-4 transition-transform group-hover:translate-x-1" />
        </div>
        <a
            href="{{ $href }}"
            wire:navigate.hover
            class="absolute inset-0 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
            aria-label="Abrir {{ $titulo }}"
        ></a>
    @endif
</flux:card>
