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

<div {{ $attributes->merge(['class' => $overlay_class]) }}>
    {{ $backdrop ?? '' }}
    <div @click.stop class="{{ $panel_class }}">
        {{ $slot }}
    </div>
</div>
