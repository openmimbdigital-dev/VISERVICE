<div>
    <header class="mb-8 border-l-4 border-indigo-600 pl-4 sm:pl-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
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
                        </p>
                    </div>
                @endif
            </div>
            <a href="{{ route('public.participants.events', ['businessToken' => $business_token]) }}"
                wire:navigate
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-medium text-slate-600 transition hover:bg-slate-50 sm:w-auto">
                Volver a agenda
            </a>
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
                    <dt class="text-xs font-medium text-slate-500">{{ org_term('Negocio', $event->business) }}</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event->business?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Descripción</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event->description ?: 'Sin descripción.' }}</dd>
                </div>
                @if($event->teams->isNotEmpty())
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Equipos</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">
                            <ul class="flex flex-col gap-1">
                                @foreach($event->teams as $team)
                                    <li>{{ $team->name }}</li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                @endif
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
</div>
