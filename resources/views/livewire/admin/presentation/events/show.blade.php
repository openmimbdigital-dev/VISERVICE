<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.events.schedule.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Agenda</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $event['title'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Eventos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $event['title'] }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">{{ \Carbon\Carbon::parse($event['date'])->locale('es')->isoFormat('dddd D [de] MMMM') }} · {{ $event['start_time'] }} – {{ $event['end_time'] }}</p>
            </div>
            <div class="flex w-full gap-2 sm:w-auto">
                <a href="{{ route('admin.presentation.events.schedule.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Agenda</a>
                <a href="{{ route('admin.presentation.events.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Eventos</a>
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
                    <dt class="text-xs font-medium text-slate-500">Fecha</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ \Carbon\Carbon::parse($event['date'])->format('d/m/Y') }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Horario</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event['start_time'] }} – {{ $event['end_time'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Lugar</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $event['location'] }}</dd>
                </div>
            </dl>
        </section>
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Descripción</h2>
            </div>
            <div class="px-5 py-5 text-sm leading-relaxed text-slate-700">{{ $event['description'] }}</div>
        </section>
    </div>
</div>
