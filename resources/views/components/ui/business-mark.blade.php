@props([
    'business' => null,
    'size' => 'sm',
])

@php
    $box = $size === 'md' ? 'h-10 w-10 text-sm' : 'h-8 w-8 text-xs';
@endphp

@if($business?->logo_url)
    <img src="{{ $business->logo_url }}"
         alt="{{ $business->name }}"
         {{ $attributes->merge(['class' => "{$box} shrink-0 rounded-lg object-cover ring-1 ring-white/10"]) }}>
@elseif($business)
    <span {{ $attributes->merge(['class' => "flex {$box} shrink-0 items-center justify-center rounded-lg bg-violet-600 font-bold text-white ring-1 ring-white/10"]) }}>
        {{ $business->logo_initials }}
    </span>
@else
    <img src="{{ asset('images/logo-initial.jpeg') }}"
         alt="SouulBi"
         {{ $attributes->merge(['class' => "{$box} shrink-0 rounded-lg object-contain bg-white/5 p-0.5"]) }}>
@endif
