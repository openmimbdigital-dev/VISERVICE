@props(['path', 'class' => 'text-indigo-400', 'size' => 'md'])

@php
    $size_class = $size === 'sm' ? 'h-4 w-4' : 'h-5 w-5';
    $paths = preg_split('/\s*\|\s*/', trim($path));
@endphp

<svg class="{{ $size_class }} shrink-0 {{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    @foreach($paths as $d)
        @if(trim($d) !== '')
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ trim($d) }}"/>
        @endif
    @endforeach
</svg>
