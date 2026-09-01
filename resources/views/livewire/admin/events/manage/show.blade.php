<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Gestión de eventos</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.manage.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Administrar eventos</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.manage.category.index', $event_category) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">{{ $event_category->name }}</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $event->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">{{ $event_category->name }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $event->name }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Detalle del evento.</p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.events.manage.category.index', $event_category) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Volver</a>
                @if($can_view_schedule)
                    <a href="{{ route('admin.events.schedule.show', $event) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Ver en agenda</a>
                @endif
                @if($can_edit)
                    @if($edit_disabled)
                        <button
                            type="button"
                            disabled
                            title="{{ $edit_disabled_title }}"
                            class="btn btn-primary btn-sm flex-1 justify-center opacity-50 sm:flex-none"
                        >
                            Editar
                        </button>
                    @else
                        <a href="{{ route('admin.events.manage.category.edit', [$event_category, $event]) }}" wire:navigate class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">Editar</a>
                    @endif
                @endif
                @if($can_delete)
                    @if($delete_disabled)
                        <button
                            type="button"
                            disabled
                            title="{{ $delete_disabled_title }}"
                            class="btn btn-danger btn-sm flex-1 justify-center opacity-50 sm:flex-none"
                        >
                            Eliminar
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="delete"
                            wire:confirm="¿Eliminar este evento?"
                            class="btn btn-danger btn-sm flex-1 justify-center sm:flex-none disabled:opacity-50"
                        >
                            Eliminar
                        </button>
                    @endif
                @endif
            </div>
        </div>
        @if($edit_disabled || $delete_disabled)
            <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Este evento ya tiene toma de asistencia iniciada; no se puede editar ni eliminar.
            </p>
        @endif
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
                    <dt class="text-xs font-medium text-slate-500">Estado</dt>
                    <dd class="text-sm sm:col-span-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $event->active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/15' }}">
                            {{ $event->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </dd>
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
                            {{ $event->teams->pluck('name')->join(', ') }}
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
                @if($event->multi_day)
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Tipo</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">Multi-día</dd>
                    </div>
                @endif
            </dl>
        </section>
    </div>

    @if($event->multi_day && $event->children->isNotEmpty())
        <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Días del evento</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50/80 text-xs font-medium uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-3 sm:px-5">Fecha</th>
                            <th class="px-3 py-3 sm:px-5">Día</th>
                            <th class="px-3 py-3 sm:px-5">Horario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($event->children as $child)
                            <tr>
                                <td class="px-3 py-4 text-slate-900 sm:px-5">{{ $child->date_start?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-3 py-4 text-slate-700 sm:px-5">{{ $child->day ?: '—' }}</td>
                                <td class="px-3 py-4 text-slate-700 sm:px-5">{{ $child->scheduleRangeLabel() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
