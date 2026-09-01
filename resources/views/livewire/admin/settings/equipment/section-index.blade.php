<div class="relative mx-auto w-full max-w-[90rem]">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex items-center gap-x-2 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.equipment.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Configuración</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.equipment.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Equipos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $config['title'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Configuración · Equipos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $config['title'] }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">{{ $config['description'] }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <a href="{{ route('admin.settings.equipment.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
                @if(!empty($config['create_route']) && auth()->user()->can('settings.attributes.create'))
                <a href="{{ route($config['create_route']) }}" wire:navigate class="btn btn-primary btn-sm">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ $config['create_button_text'] ?? $config['button_text'] ?? 'Nuevo' }}
                </a>
                @endif
            </div>
        </div>
    </header>

    @if(!empty($config['datatable_component']))
    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Listado de {{ strtolower($config['title']) }}</h2>
        </div>
        <div class="p-4">
            <livewire:dynamic-component :component="$config['datatable_component']" :key="$section . '-datatable'" />
        </div>
    </section>
    @else
    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex flex-col items-center justify-center px-8 py-16 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl {{ $config['icon_bg'] }}">
                <svg class="h-7 w-7 {{ $config['icon_c'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $config['icon'] }}"/>
                </svg>
            </div>
            <p class="mt-4 text-sm font-medium text-slate-700">Listado en construcción</p>
            <p class="mt-1 max-w-sm text-sm text-slate-500">Próximamente podrás administrar {{ strtolower($config['title']) }} desde aquí.</p>
        </div>
    </section>
    @endif
</div>
