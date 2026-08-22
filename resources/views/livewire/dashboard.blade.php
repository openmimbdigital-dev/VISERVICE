<div class="space-y-8">

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 px-8 py-10 shadow-xl">
        <div class="absolute inset-0 opacity-30" style="background-image:radial-gradient(rgba(255,255,255,.15) 1px,transparent 1px);background-size:24px 24px;"></div>
        <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-indigo-600/20 blur-3xl"></div>
        <div class="absolute -bottom-16 -left-10 h-48 w-48 rounded-full bg-primary-700/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <x-brand.mark variant="on-dark" class="h-16 w-auto drop-shadow-lg shrink-0" />
            <div>
                <p class="text-indigo-300 text-xs font-semibold uppercase tracking-widest mb-1">Panel de control</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white leading-tight">
                    Bienvenido, <span class="text-indigo-300">{{ auth()->user()?->full_name ?: auth()->user()?->username }}</span>
                </h1>
                <p class="mt-1.5 text-slate-400 text-sm">
                    Hoy es {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}.
                    Todo bajo control.
                </p>
            </div>
        </div>
    </div>

    {{-- Módulos asignados al negocio --}}
    <div>
        <h2 class="mb-4 text-xs font-semibold uppercase tracking-widest text-slate-500">Módulos del sistema</h2>
        @if($modules === [])
            <p class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center text-sm text-slate-500">
                No hay módulos asignados a este negocio.
            </p>
        @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($modules as $mod)
            <div class="flex flex-col gap-4 rounded-2xl border {{ $mod['bg'] }} p-5 shadow-sm transition-shadow hover:shadow-md">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $mod['icon_bg'] }}">
                        <svg class="h-5 w-5 {{ $mod['icon_c'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $mod['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-slate-900">{{ $mod['title'] }}</h3>
                        <p class="mt-0.5 text-xs leading-snug text-slate-500">{{ $mod['desc'] }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    @foreach($mod['links'] as $link)
                    <a href="{{ $link['url'] }}" wire:navigate
                        class="group flex items-center gap-2 rounded-lg border border-slate-200/80 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400 transition group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        {{ $link['label'] }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Estado del sistema --}}
    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></div>
            <span class="text-sm text-slate-600">Sistema operativo y en línea</span>
        </div>
        <span class="text-xs text-slate-400">SouulBi v1.0 · Laravel 12 + Livewire 3</span>
    </div>

</div>
