<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.reports.events.attendance.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Reportes</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.reports.events.attendance.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Asistencia de eventos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $event_category->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Reportes</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $event_category->name }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Categoría {{ strtolower($event_category->type?->label() ?? '') }} · {{ $events_count }} evento(s).
                </p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.reports.events.attendance.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                    Volver
                </a>
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="space-y-4 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-semibold text-slate-800">Listado de eventos</h2>
                @if($has_filters)
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="text-xs font-medium text-indigo-600 transition hover:text-indigo-700"
                    >
                        Limpiar filtros
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <label for="report-events-name-filter" class="mb-1.5 block text-xs font-medium text-slate-600">Nombre</label>
                    <input
                        id="report-events-name-filter"
                        type="text"
                        wire:model.live.debounce.400ms="name"
                        placeholder="Buscar por nombre"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    >
                </div>
                <div>
                    <label for="report-events-date-filter" class="mb-1.5 block text-xs font-medium text-slate-600">Fecha</label>
                    <input
                        id="report-events-date-filter"
                        type="date"
                        wire:model.live="date"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    >
                </div>
                <div>
                    <label for="report-events-month-filter" class="mb-1.5 block text-xs font-medium text-slate-600">Mes</label>
                    <input
                        id="report-events-month-filter"
                        type="number"
                        min="1"
                        max="12"
                        wire:model.live.debounce.400ms="month"
                        placeholder="1-12"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    >
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Evento</th>
                        <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Fecha</th>
                        <th class="hidden px-3 py-3 md:table-cell sm:px-5">Día</th>
                        <th class="hidden px-3 py-3 lg:table-cell sm:px-5">Horario</th>
                        <th class="px-3 py-3 text-right sm:px-5">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-3 py-4 sm:px-5">
                                <p class="font-medium text-slate-900">{{ $event->name }}</p>
                                @if($event->isMultiDayChild())
                                    <p class="mt-0.5 text-xs text-indigo-700">Día de evento multi-día</p>
                                @endif
                                <p class="mt-0.5 text-xs text-slate-500 sm:hidden">
                                    {{ $event->dateRangeLabel() }}
                                </p>
                            </td>
                            <td class="hidden px-3 py-4 text-slate-700 sm:table-cell sm:px-5">
                                {{ $event->dateRangeLabel() }}
                                @if($event->isMultiDayChild() && $event->parent)
                                    <p class="mt-0.5 text-xs text-indigo-600">{{ $event->parent->dateRangeLabel() }}</p>
                                @endif
                            </td>
                            <td class="hidden px-3 py-4 text-slate-700 md:table-cell sm:px-5">
                                {{ $event->day ?: '—' }}
                            </td>
                            <td class="hidden px-3 py-4 text-slate-700 lg:table-cell sm:px-5">
                                {{ $event->scheduleRangeLabel() }}
                            </td>
                            <td class="px-3 py-4 sm:px-5">
                                <div class="flex flex-wrap justify-end gap-1">
                                    <a
                                        href="{{ route('admin.reports.events.attendance.show', [$event_category, $event]) }}"
                                        wire:navigate
                                        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-indigo-700 transition hover:bg-indigo-50"
                                        title="Ver estadística"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        <span class="hidden sm:inline">Ver estadística</span>
                                    </a>
                                    @if($can_export)
                                        <button
                                            type="button"
                                            wire:click="exportAttendance({{ $event->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="exportAttendance({{ $event->id }})"
                                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-50 disabled:opacity-50"
                                            title="Exportar asistencia en Excel"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span class="hidden sm:inline">Exportar Excel</span>
                                        </button>
                                        <a
                                            href="{{ route('admin.reports.events.attendance.pdf', [$event_category, $event]) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-50"
                                            title="Exportar asistencia en PDF"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="hidden sm:inline">Exportar PDF</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">
                                No hay eventos{{ $has_filters ? ' con los filtros aplicados' : ' en esta categoría' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div class="border-t border-slate-100 px-4 py-3 sm:px-5">
                {{ $events->links() }}
            </div>
        @endif
    </section>
</div>
