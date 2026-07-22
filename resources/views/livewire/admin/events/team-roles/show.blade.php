<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.team-roles.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Evento</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.team-roles.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Roles del equipo</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $event_team_role->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Evento</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $event_team_role->name }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Detalle del rol y de los equipos que lo utilizan.</p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.events.team-roles.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Volver</a>
                @if($can_edit)
                    <a href="{{ route('admin.events.team-roles.edit', $event_team_role) }}" wire:navigate class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">Editar</a>
                @endif
                @if($can_delete)
                    <button
                        type="button"
                        wire:click="delete"
                        wire:confirm="¿Eliminar este rol del equipo?"
                        class="btn btn-danger btn-sm flex-1 justify-center sm:flex-none disabled:opacity-50"
                    >
                        Eliminar
                    </button>
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
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event_team_role->name }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Estado</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">
                        @if($event_team_role->active)
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">Activo</span>
                        @else
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">Inactivo</span>
                        @endif
                    </dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Iglesia</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event_team_role->business?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Asignaciones</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event_team_role->members_count }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Funciones</h2>
            </div>
            <p class="px-5 py-4 text-sm leading-relaxed text-slate-700">{{ $event_team_role->functions ?: 'Sin funciones definidas.' }}</p>
        </section>
    </div>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Equipos que usan este rol</h2>
        </div>
        <ul class="divide-y divide-slate-100 px-5 py-2">
            @forelse($event_team_role->teams as $team)
                <li class="py-3">
                    <a href="{{ route('admin.events.teams.show', $team) }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                        {{ $team->name }}
                    </a>
                </li>
            @empty
                <li class="py-3 text-sm text-slate-500">Ningún equipo usa este rol todavía.</li>
            @endforelse
        </ul>
    </section>
</div>
