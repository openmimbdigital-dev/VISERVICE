@props([
    'maxWidth' => 'lg',
    'centered' => false,
])

@php
    $widths = [
        'sm'  => 'sm:max-w-sm',
        'md'  => 'sm:max-w-md',
        'lg'  => 'sm:max-w-lg',
        'xl'  => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ];
    $panel_width = $widths[$maxWidth] ?? $widths['lg'];

    $overlay_class = $centered
        ? 'fixed inset-0 z-50 flex items-start justify-center p-4 pt-10 sm:pt-16'
        : 'fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4';

    $panel_class = $centered
        ? "relative z-10 flex w-full max-h-[85vh] flex-col overflow-hidden rounded-2xl bg-white shadow-xl {$panel_width}"
        : "relative z-10 flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:max-h-[90vh] sm:rounded-2xl {$panel_width}";
@endphp

{{--
    El modal lo monta y desmonta Livewire, así que la animación de entrada se dispara
    al inicializar Alpine. La tecla Escape reutiliza el clic del backdrop, de modo que
    cada pantalla conserva su propio método de cierre.
--}}
<div
    x-data="{ open: false }"
    x-init="$nextTick(() => open = true)"
    x-trap.noscroll="open"
    x-on:keydown.escape.window="$refs.backdrop?.firstElementChild?.click()"
    {{ $attributes->merge(['class' => $overlay_class]) }}
>
    <div
        x-ref="backdrop"
        x-cloak
        x-show="open"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="absolute inset-0"
    >
        {{ $backdrop ?? '' }}
    </div>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        @click.stop
        class="{{ $panel_class }}"
    >
        {{ $slot }}
    </div>
</div>
