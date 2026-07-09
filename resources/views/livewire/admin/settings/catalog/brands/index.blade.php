<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.catalog-products.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Configuración</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.catalog-products.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Productos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $config['title'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Configuración · Productos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $config['title'] }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">{{ $config['description'] }}</p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.settings.catalog-products.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
                @can('settings.brands.create')
                <x-ui.create-button
                    :href="route('admin.settings.catalog-products.brands.create')"
                    size="sm"
                    class="flex-1 sm:flex-none justify-center"
                >
                    {{ $config['create_button_text'] ?? 'Nueva marca' }}
                </x-ui.create-button>
                @endcan
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado de marcas</h2>
        </div>
        <div class="overflow-x-auto p-3 sm:p-4">
            <livewire:dynamic-component :component="$config['datatable_component']" :key="'catalog-brands-datatable'" />
        </div>
    </section>
</div>
