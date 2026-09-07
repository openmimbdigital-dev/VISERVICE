@props([
    'product',
    'size' => 'md',
])

@php
    $size_classes = match ($size) {
        'sm' => 'h-10 w-10 rounded-xl border border-slate-200',
        'md' => 'h-14 w-14 rounded-xl border border-slate-200',
        'lg' => 'h-24 w-24 rounded-xl border border-slate-200',
        'xl' => 'aspect-square w-full',
        default => $size,
    };

    $url = $product?->primaryImageUrl();
@endphp

<div {{ $attributes->merge([
    'class' => "flex shrink-0 items-center justify-center overflow-hidden bg-slate-100 {$size_classes}",
]) }}>
    @if($url)
        <img src="{{ $url }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
    @else
        <svg class="h-1/2 w-1/2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 6h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z"/>
            <circle cx="9" cy="9" r="1.5"/>
        </svg>
    @endif
</div>
