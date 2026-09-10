<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.academic.classroom.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Mi aula</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.academic.classroom.show', $subject['id']) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">{{ $subject['name'] }}</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $type_label }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">{{ $subject['name'] }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $type_label }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Entra a una {{ mb_strtolower($type_label) }} para resolverla. También puedes crear una nueva.</p>
            </div>
            <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row">
                <a href="{{ route('admin.presentation.academic.classroom.show', $subject['id']) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Volver</a>
                <x-ui.create-button :href="route('admin.presentation.academic.classroom.activities.create', [$subject['id'], $activity_type])" size="sm" class="flex-1 justify-center sm:flex-none">
                    Nueva {{ mb_strtolower($type_label) }}
                </x-ui.create-button>
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Actividad</th>
                        <th class="hidden px-3 py-3 md:table-cell sm:px-5">Entrega</th>
                        <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Estado</th>
                        <th class="px-3 py-3 sm:px-5"><span class="sr-only">Resolver</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activities as $activity)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-4 sm:px-5">
                            <p class="font-medium text-slate-900">{{ $activity['title'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $activity['description'] }}</p>
                            <p class="mt-1 text-xs text-slate-500 sm:hidden">{{ \Carbon\Carbon::parse($activity['due_date'])->format('d/m/Y') }}</p>
                        </td>
                        <td class="hidden px-3 py-4 text-slate-600 md:table-cell sm:px-5">{{ \Carbon\Carbon::parse($activity['due_date'])->format('d/m/Y') }}</td>
                        <td class="hidden px-3 py-4 sm:table-cell sm:px-5">
                            @if($activity['submitted'])
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">Entregada</span>
                            @else
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-right sm:px-5">
                            <a href="{{ route('admin.presentation.academic.classroom.activities.solve', [$subject['id'], $activity_type, $activity['id']]) }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:underline">
                                {{ $activity['submitted'] ? 'Ver' : 'Ingresar' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-10 text-center text-sm text-slate-500 sm:px-5">No hay {{ mb_strtolower($type_label) }}s aún. Crea la primera.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
