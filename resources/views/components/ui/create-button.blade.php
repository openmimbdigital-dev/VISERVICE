@props([
    'href' => null,
    'size' => 'md',
])

@php
    $base = 'inline-flex shrink-0 items-center gap-2 rounded-xl bg-indigo-600 font-semibold text-white shadow-sm shadow-indigo-500/20 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50';

    $sizes = [
        'md' => 'px-4 py-2.5 text-sm',
        'sm' => 'px-3.5 py-2 text-sm',
    ];

    $classes = trim($base . ' ' . ($sizes[$size] ?? $sizes['md']));
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }}>
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ $slot }}
    </button>
@endif
