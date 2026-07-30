<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.reports.events.attendance.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Reportes</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.reports.events.attendance.category', $event_category) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">{{ $event_category->name }}</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $event->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Estadística de asistencia</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $event->name }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    {{ $event->dateRangeLabel() }} · {{ $event->scheduleRangeLabel() }}
                </p>
                @if($event->isMultiDayChild() && $event->parent)
                    <div class="mt-3 rounded-xl border border-indigo-100 bg-indigo-50/80 px-3.5 py-3 text-sm text-indigo-900">
                        <p class="font-medium">Día de un evento multi-día</p>
                        <p class="mt-1 text-indigo-800/90">
                            {{ $event->multiDayContextLabel() }}
                            Esta estadística y gráfica corresponden únicamente a este día.
                        </p>
                    </div>
                @endif
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.reports.events.attendance.category', $event_category) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Volver</a>
            </div>
        </div>
    </header>

    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Asistencia total</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-emerald-600">{{ number_format($attendance_total, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Tipos registrados</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-slate-900">{{ $attendance_rows->count() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Estado</p>
            <p class="mt-2 text-sm font-semibold text-slate-900">
                @if($event->attendance_closed)
                    Cerrada
                @elseif($attendance_rows->isNotEmpty())
                    En curso / iniciada
                @else
                    Sin registro
                @endif
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Información del evento</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event->name }}</dd>
                </div>
                @if($event->isMultiDayChild() && $event->parent)
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Evento padre</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">
                            {{ $event->parent->name }}
                            <span class="text-slate-500">({{ $event->parent->dateRangeLabel() }})</span>
                        </dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Tipo</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">Día de evento multi-día</dd>
                    </div>
                @endif
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Categoría</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event->category?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Iglesia</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event->business?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Descripción</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event->description ?: 'Sin descripción.' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Fecha</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event->dateRangeLabel() }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Día</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event->day ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Horario</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event->scheduleRangeLabel() }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Equipos</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">
                        @if($event->teams->isEmpty())
                            Sin equipos asignados.
                        @else
                            {{ $event->teams->pluck('name')->join(', ') }}
                        @endif
                    </dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Detalle por tipo de asistencia</h2>
            </div>
            <div class="px-5 py-4">
                @if($attendance_rows->isEmpty())
                    <p class="text-sm text-slate-500">Este evento aún no tiene toma de asistencia registrada.</p>
                @else
                    <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200">
                        @foreach($attendance_rows as $attendee_type)
                            <li class="flex items-center justify-between gap-3 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-900">{{ $attendee_type->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $attendee_type->ageRangeLabel() }}</p>
                                </div>
                                <span class="text-lg font-semibold tabular-nums text-indigo-700">
                                    {{ (int) $attendee_type->pivot->attendance }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-slate-800">Gráfico de asistencia</h2>
                <p class="mt-1 text-xs text-slate-500">
                    @if($event->isMultiDayChild())
                        Totales de este día del evento multi-día.
                    @else
                        Totales registrados para este evento.
                    @endif
                </p>
            </div>
            <button
                type="button"
                wire:click="refreshAttendanceChart"
                wire:loading.attr="disabled"
                class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto"
            >
                <span wire:loading.remove wire:target="refreshAttendanceChart">Actualizar gráfico</span>
                <span wire:loading wire:target="refreshAttendanceChart">Actualizando...</span>
            </button>
        </div>

        <div
            class="px-4 py-5 sm:px-5"
            wire:ignore
            x-data
            x-init="
                $nextTick(() => {
                    if (typeof window.renderEventAttendanceChart === 'function' && $refs.attendanceChart) {
                        window.renderEventAttendanceChart(
                            $refs.attendanceChart,
                            @js($attendance_chart_labels),
                            @js($attendance_chart_values)
                        );
                    }
                });
            "
            @attendance-chart-updated.window="
                if (typeof window.updateEventAttendanceChart === 'function' && $refs.attendanceChart) {
                    window.updateEventAttendanceChart(
                        $refs.attendanceChart,
                        $event.detail.labels ?? [],
                        $event.detail.values ?? []
                    );
                }
            "
        >
            <div x-ref="attendanceChart" class="min-h-[280px] w-full"></div>
        </div>
    </section>
</div>
