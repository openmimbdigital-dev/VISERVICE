<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Gestión de eventos</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.manage.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Administrar eventos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $event_category->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Gestión de eventos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $event_category->name }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Categoría {{ strtolower($event_category->type?->label() ?? '') }} · {{ $events_count }} evento(s).
                </p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.events.manage.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                    Volver
                </a>
                @can('events.events.create')
                    <x-ui.create-button
                        :href="route('admin.events.manage.category.create', $event_category)"
                        size="sm"
                        class="flex-1 justify-center sm:flex-none"
                    >
                        Nuevo evento
                    </x-ui.create-button>
                @endcan
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado de eventos</h2>
            <div class="flex w-full items-center gap-2 sm:w-auto">
                <label for="events-month-filter" class="shrink-0 text-xs font-medium text-slate-600">Mes</label>
                <input
                    id="events-month-filter"
                    type="number"
                    min="1"
                    max="12"
                    wire:model.live.debounce.400ms="month"
                    placeholder="1-12"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 sm:w-28"
                >
            </div>
        </div>
        <div class="overflow-x-auto p-3 sm:p-4">
            <livewire:admin.events.manage.datatable-events
                :event_category_id="$event_category->id"
                :month="$month_filter"
                :key="'events-manage-datatable-'.$event_category->id.'-'.($month_filter ?? 'all')"
            />
        </div>
    </section>
</div>
