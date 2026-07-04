@props([
    'business',
    'size' => 'md',
])

@php
    $size_classes = match ($size) {
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-16 w-16 text-xl',
        'xl' => 'h-20 w-20 text-2xl',
        default => $size,
    };
@endphp

<div {{ $attributes->merge([
    'class' => "flex shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 {$size_classes}",
]) }}>
    @if($business->logo_url)
        <img src="{{ $business->logo_url }}" alt="Logo de {{ $business->name }}" class="h-full w-full object-cover">
    @else
        <span class="font-bold text-slate-400">{{ $business->logo_initials }}</span>
    @endif
</div>
