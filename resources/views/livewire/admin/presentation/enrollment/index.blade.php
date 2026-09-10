<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Presentación</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Inscripción</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Inscripción</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Inscripción de estudiantes</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Los acudientes inscriben a sus hijos, cargan documentos y registran el pago. Datos de demostración.</p>
            </div>
            <x-ui.create-button :href="route('admin.presentation.enrollment.create')" class="w-full justify-center sm:w-auto">
                Nueva inscripción
            </x-ui.create-button>
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach([
            ['key' => 'completed', 'label' => 'Inscritos', 'color' => 'text-emerald-600'],
            ['key' => 'documents_pending', 'label' => 'Documentos pendientes', 'color' => 'text-amber-600'],
            ['key' => 'pending_payment', 'label' => 'Pago pendiente', 'color' => 'text-rose-600'],
        ] as $stat)
        <button type="button" wire:click="$set('filter_status', '{{ $filter_status === $stat['key'] ? '' : $stat['key'] }}')"
            class="rounded-2xl border bg-white p-4 text-left shadow-sm {{ $filter_status === $stat['key'] ? 'border-transparent ring-2 ring-indigo-500' : 'border-slate-200/90 ring-1 ring-slate-900/[0.04]' }}">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums {{ $stat['color'] }}">{{ $stats[$stat['key']] }}</p>
        </button>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Solicitudes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Estudiante</th>
                        <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Acudiente</th>
                        <th class="hidden px-3 py-3 md:table-cell sm:px-5">Documentos</th>
                        <th class="px-3 py-3 sm:px-5">Estado</th>
                        <th class="px-3 py-3 sm:px-5"><span class="sr-only">Ver</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($enrollments as $row)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-4 sm:px-5">
                            <p class="font-medium text-slate-900">{{ $row['child_name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $row['grade'] }} · {{ $row['shift_label'] }}</p>
                        </td>
                        <td class="hidden px-3 py-4 text-slate-700 sm:table-cell sm:px-5">{{ $row['parent_name'] }}</td>
                        <td class="hidden px-3 py-4 tabular-nums text-slate-700 md:table-cell sm:px-5">{{ $row['documents_uploaded'] }}/{{ $row['documents_total'] }}</td>
                        <td class="px-3 py-4 sm:px-5">
                            @php
                                $badge = match ($row['status']) {
                                    'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                    'pending_payment' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                                    default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $row['status_label'] }}</span>
                        </td>
                        <td class="px-3 py-4 text-right sm:px-5">
                            <a href="{{ route('admin.presentation.enrollment.show', $row['id']) }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-3 py-10 text-center text-sm text-slate-500 sm:px-5">No hay inscripciones con ese filtro.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
