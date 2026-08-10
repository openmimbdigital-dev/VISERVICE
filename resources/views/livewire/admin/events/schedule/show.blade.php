<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Gestión de eventos</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.schedule.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Agenda</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $event->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Agenda de eventos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $event->name }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Información del evento seleccionado en la agenda.</p>
                @if($event->parent_id && $event->parent)
                    <div class="mt-3 rounded-xl border border-indigo-100 bg-indigo-50/80 px-3.5 py-3 text-sm text-indigo-900">
                        <p class="font-medium">Día de un evento multi-día</p>
                        <p class="mt-1 text-indigo-800/90">
                            Este registro corresponde al día
                            <span class="font-semibold">{{ $event->date_start?->format('d/m/Y') ?? '—' }}</span>
                            del evento
                            <span class="font-semibold">«{{ $event->parent->name }}»</span>
                            @if($event->parent->date_start && $event->parent->date_end)
                                ({{ $event->parent->dateRangeLabel() }}).
                            @else
                                .
                            @endif
                            La toma de asistencia o participación aplica solo a este día.
                        </p>
                    </div>
                @endif
                <p class="mt-3 text-sm text-slate-700">
                    <span class="font-medium text-slate-900">Fecha y hora actual:</span>
                    {{ ucfirst($now_date_label) }} · {{ $now_time_label }}
                </p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.events.schedule.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                    Volver a agenda
                </a>
                @if($can_manage)
                    <a href="{{ route('admin.events.manage.category.show', [$event->category, $event->parent ?? $event]) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                        Ver en administración
                    </a>
                @endif
                @if($can_edit)
                    @if($edit_disabled)
                        <button
                            type="button"
                            disabled
                            title="{{ $edit_disabled_title }}"
                            class="btn btn-primary btn-sm flex-1 justify-center opacity-50 sm:flex-none"
                        >
                            Editar evento
                        </button>
                    @else
                        <a href="{{ route('admin.events.manage.category.edit', [$event->category, $event->parent ?? $event]) }}" wire:navigate class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">
                            Editar evento
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Información</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event->name }}</dd>
                </div>
                @if($event->parent_id && $event->parent)
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Evento padre</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">
                            {{ $event->parent->name }}
                            <span class="text-slate-500">({{ $event->parent->dateRangeLabel() }})</span>
                        </dd>
                    </div>
                @endif
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Categoría</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">
                        {{ $event->category?->name ?? '—' }}
                        @if($event->category?->type)
                            <span class="text-slate-500">({{ $event->category->type->label() }})</span>
                        @endif
                    </dd>
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
                    <dt class="text-xs font-medium text-slate-500">Toma de asistencia</dt>
                    <dd class="text-sm sm:col-span-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $event->attendance_enabled ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/15' }}">
                            {{ $event->attendance_enabled ? 'Activa' : 'Inactiva' }}
                        </span>
                    </dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Toma de participación</dt>
                    <dd class="text-sm sm:col-span-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $event->participation_enabled ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/15' }}">
                            {{ $event->participation_enabled ? 'Activa' : 'Inactiva' }}
                        </span>
                    </dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Equipos</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">
                        @if($event->teams->isEmpty())
                            Sin equipos asignados.
                        @else
                            <ul class="flex flex-col gap-1.5">
                                @foreach($event->teams as $team)
                                    <li>
                                        <a
                                            href="{{ route('admin.events.teams.show', ['eventTeam' => $team, 'from_event' => $event->id]) }}"
                                            wire:navigate
                                            class="font-medium text-indigo-600 transition hover:text-indigo-700 hover:underline"
                                        >
                                            {{ $team->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Fecha y horario</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
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
            </dl>
        </section>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Toma de asistencia</h2>
                <p class="mt-1 text-xs text-slate-500">Disponible solo el día del evento y durante su horario.</p>
            </div>
            <div class="px-5 py-5">
                @if(! $attendance_capture['available'])
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4">
                        <p class="text-sm font-medium text-amber-900">Por ahora no puedes tomar asistencia</p>
                        <p class="mt-1 text-sm text-amber-800/90">{{ $attendance_capture['message'] }}</p>
                    </div>
                @elseif(! $can_start_attendance)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4">
                        <p class="text-sm font-medium text-amber-900">Sin permiso</p>
                        <p class="mt-1 text-sm text-amber-800/90">No tienes permiso para iniciar la toma de asistencia de este evento.</p>
                    </div>
                @elseif($attendance_started)
                    @if($attendance_closed)
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 sm:flex-1">
                                <p class="text-sm font-medium text-slate-800">Toma de asistencia cerrada</p>
                                <p class="mt-1 text-xs text-slate-500">Los contadores quedaron bloqueados. Aún puedes consultar el gráfico.</p>
                            </div>
                            @if($can_close_attendance)
                                <button
                                    type="button"
                                    wire:click="confirmReopenAttendance"
                                    wire:loading.attr="disabled"
                                    class="btn btn-primary btn-sm w-full shrink-0 justify-center sm:w-auto"
                                >
                                    <span wire:loading.remove wire:target="confirmReopenAttendance,reopenAttendance">Desbloquear asistencia</span>
                                    <span wire:loading wire:target="confirmReopenAttendance,reopenAttendance">Desbloqueando...</span>
                                </button>
                            @endif
                        </div>
                    @elseif($can_close_attendance)
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-slate-500">Cuando termines el conteo, cierra la toma de asistencia.</p>
                            <button
                                type="button"
                                wire:click="confirmCloseAttendance"
                                wire:loading.attr="disabled"
                                class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto"
                            >
                                <span wire:loading.remove wire:target="confirmCloseAttendance,closeAttendance">Cerrar toma de asistencia</span>
                                <span wire:loading wire:target="confirmCloseAttendance,closeAttendance">Cerrando...</span>
                            </button>
                        </div>
                    @endif

                    <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200">
                        @foreach($attendance_rows as $attendee_type)
                            <li class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between" wire:key="attendance-row-{{ $attendee_type->id }}">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-900">{{ $attendee_type->name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $attendee_type->ageRangeLabel() }}</p>
                                </div>
                                <div class="flex items-center justify-between gap-3 sm:justify-end">
                                    <span class="min-w-[3rem] text-center text-lg font-semibold tabular-nums text-slate-900">
                                        {{ (int) $attendee_type->pivot->attendance }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            wire:click="decrementAttendance({{ $attendee_type->id }})"
                                            @disabled($attendance_closed || (int) $attendee_type->pivot->attendance <= 0)
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                                            title="Decrementar"
                                        >
                                            <span class="text-lg leading-none">−</span>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="incrementAttendance({{ $attendee_type->id }})"
                                            @disabled($attendance_closed)
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700 transition hover:bg-indigo-100 disabled:cursor-not-allowed disabled:opacity-40"
                                            title="Incrementar"
                                        >
                                            <span class="text-lg leading-none">+</span>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="space-y-4">
                        @if($attendance_closed)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-sm font-medium text-slate-800">Toma de asistencia cerrada</p>
                                <p class="mt-1 text-xs text-slate-500">No es posible iniciar nuevamente la toma de asistencia en este evento.</p>
                            </div>
                        @else
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">
                                Tipos de asistencia <span class="text-rose-500">*</span>
                            </label>
                            @if($attendee_type_options->isEmpty())
                                <p class="text-sm text-slate-500">No hay tipos de asistencia disponibles.</p>
                            @else
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    @foreach($attendee_type_options as $type)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-sm text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                                            <input
                                                type="checkbox"
                                                wire:model="selected_attendee_type_ids"
                                                value="{{ $type->id }}"
                                                class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30"
                                            >
                                            <span class="min-w-0">
                                                <span class="block font-medium text-slate-800">{{ $type->name }}</span>
                                                <span class="mt-0.5 block text-xs text-slate-500">{{ $type->ageRangeLabel() }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            @error('selected_attendee_type_ids') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            @error('selected_attendee_type_ids.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <button
                            type="button"
                            wire:click="startAttendance"
                            wire:loading.attr="disabled"
                            @disabled($attendee_type_options->isEmpty())
                            class="btn btn-primary w-full justify-center disabled:opacity-60 sm:w-auto"
                        >
                            <span wire:loading.remove wire:target="startAttendance">Iniciar asistencia</span>
                            <span wire:loading wire:target="startAttendance">Iniciando...</span>
                        </button>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Toma de participación</h2>
                <p class="mt-1 text-xs text-slate-500">Disponible solo el día del evento y durante su horario.</p>
            </div>
            <div class="px-5 py-5">
                @if($participation_capture['available'])
                    <div class="rounded-xl border border-dashed border-indigo-200 bg-indigo-50/40 px-4 py-6 text-center">
                        <p class="text-sm font-medium text-indigo-800">Sección lista para tomar participación</p>
                        <p class="mt-1 text-xs text-indigo-600/80">Aquí irá el registro de participación en una próxima fase.</p>
                    </div>
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4">
                        <p class="text-sm font-medium text-amber-900">Por ahora no puedes tomar participación</p>
                        <p class="mt-1 text-sm text-amber-800/90">{{ $participation_capture['message'] }}</p>
                    </div>
                @endif
            </div>
        </section>
    </div>

    @if($attendance_started)
        <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold text-slate-800">Gráfico de asistencia</h2>
                    <p class="mt-1 text-xs text-slate-500">Consulta los totales registrados hasta el momento.</p>
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
                <div x-ref="attendanceChart" class="min-h-[17.5rem] w-full"></div>
            </div>
        </section>
    @endif
</div>
