<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex items-center gap-x-2 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Configuración</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Productos</span>
    </nav>

    <header class="mb-8">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Configuración</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Productos del catálogo</h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">Administra tipos, categorías, unidades y marcas para el catálogo de productos.</p>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($sections as $section)
        <article class="group flex min-h-[220px] flex-col rounded-2xl border p-5 shadow-sm ring-1 ring-slate-900/[0.035] transition-all hover:shadow-md {{ $section['card_bg'] }}">
            <div class="flex items-start gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $section['icon_bg'] }} ring-1 ring-black/[0.04] transition-transform group-hover:scale-105">
                    <svg class="h-5 w-5 {{ $section['icon_c'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="font-semibold text-slate-900">{{ $section['title'] }}</h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $section['description'] }}</p>
                </div>
            </div>

            <div class="mt-auto pt-6">
                <a href="{{ route($section['route']) }}" wire:navigate
                    class="btn {{ $section['btn_class'] }} btn-sm w-full justify-center">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    {{ $section['button_text'] }}
                </a>
            </div>
        </article>
        @endforeach
    </div>
</div>
