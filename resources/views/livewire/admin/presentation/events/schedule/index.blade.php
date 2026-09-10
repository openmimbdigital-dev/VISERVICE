<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Agenda</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Eventos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Agenda</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Consulta los eventos del mes. Entra a uno para ver su descripción.</p>
            </div>
            <x-ui.create-button :href="route('admin.presentation.events.create')" class="w-full justify-center sm:w-auto">Nuevo evento</x-ui.create-button>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
            <h2 class="text-lg font-semibold capitalize text-slate-900">{{ $month_label }}</h2>
            <div class="flex gap-2">
                <button type="button" wire:click="previousMonth" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50">Anterior</button>
                <button type="button" wire:click="goToday" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50">Hoy</button>
                <button type="button" wire:click="nextMonth" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50">Siguiente</button>
            </div>
        </div>
        <div class="overflow-x-auto p-3 sm:p-4">
            <div class="grid min-w-[48rem] grid-cols-7 gap-px rounded-xl bg-slate-200">
                @foreach($weekday_labels as $label)
                <div class="bg-slate-50 px-2 py-2 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</div>
                @endforeach
                @foreach($weeks as $week)
                    @foreach($week as $day)
                    <div class="min-h-[7rem] bg-white p-2 {{ $day['in_month'] ? '' : 'bg-slate-50/80' }} {{ $day['is_today'] ? 'ring-2 ring-inset ring-indigo-400' : '' }}">
                        <p class="text-xs font-semibold {{ $day['in_month'] ? 'text-slate-700' : 'text-slate-300' }}">{{ $day['day'] }}</p>
                        <div class="mt-1 space-y-1">
                            @foreach($day['events'] as $event)
                            <a href="{{ route('admin.presentation.events.show', $event['id']) }}" wire:navigate
                                class="block truncate rounded-md bg-indigo-50 px-1.5 py-1 text-[11px] font-medium text-indigo-700 hover:bg-indigo-100">
                                {{ $event['start_time'] }} {{ $event['title'] }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </section>
</div>
