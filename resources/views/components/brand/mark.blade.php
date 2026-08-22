@props(['variant' => 'on-dark'])
@php
    $arch = $variant === 'on-light' ? '#332B8A' : '#D7D3FA';
    $accent = $variant === 'on-light' ? '#4F46E5' : '#665CEC';
@endphp
<svg {{ $attributes->merge(['class' => 'inline-block']) }} viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="SouulBi">
    <path d="M29,77 L29,43 A21,21 0 0 1 71,43 L71,58" stroke="{{ $arch }}" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M79,54 L91,29" stroke="{{ $accent }}" stroke-width="9" stroke-linecap="round"/>
    <circle cx="92" cy="25" r="6.5" fill="{{ $accent }}"/>
</svg>
