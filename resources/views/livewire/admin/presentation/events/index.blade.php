<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Eventos</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Eventos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Eventos</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Crea eventos de demostración. Luego aparecen en la agenda.</p>
            </div>
            <x-ui.create-button :href="route('admin.presentation.events.create')" class="w-full justify-center sm:w-auto">Nuevo evento</x-ui.create-button>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Evento</th>
                        <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Fecha</th>
                        <th class="hidden px-3 py-3 md:table-cell sm:px-5">Lugar</th>
                        <th class="px-3 py-3 sm:px-5"><span class="sr-only">Ver</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($events as $event)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-4 sm:px-5">
                            <p class="font-medium text-slate-900">{{ $event['title'] }}</p>
                            <p class="text-xs text-slate-500 sm:hidden">{{ \Carbon\Carbon::parse($event['date'])->format('d/m/Y') }} · {{ $event['start_time'] }}</p>
                        </td>
                        <td class="hidden px-3 py-4 text-slate-700 sm:table-cell sm:px-5">{{ \Carbon\Carbon::parse($event['date'])->format('d/m/Y') }} · {{ $event['start_time'] }} – {{ $event['end_time'] }}</td>
                        <td class="hidden px-3 py-4 text-slate-700 md:table-cell sm:px-5">{{ $event['location'] }}</td>
                        <td class="px-3 py-4 text-right sm:px-5">
                            <a href="{{ route('admin.presentation.events.show', $event['id']) }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
