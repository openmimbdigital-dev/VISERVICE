<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.reports.index', ['section' => 'courses']) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Reportes</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $report['name'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Reportes</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $report['name'] }}</h1>
                    <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">{{ $report['code'] }}</span>
                </div>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Reporte de notas del curso. Miss {{ $report['teacher'] }} · {{ $report['shift_label'] }}.</p>
            </div>
            <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row">
                <a href="{{ route('admin.presentation.reports.index', ['section' => 'courses']) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Volver</a>
                <a href="{{ route('admin.presentation.reports.courses.pdf.show', $report['id']) }}" class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Exportar PDF</a>
                <button type="button" wire:click="openEmailModal" class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">Enviar por correo</button>
            </div>
        </div>
    </header>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Estudiantes</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ $report['students'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Promedio</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums {{ $report['average'] >= 3.5 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($report['average'], 1) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Aprobados</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-emerald-600">{{ $report['passed'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">En riesgo</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-rose-600">{{ $report['at_risk'] }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Notas por estudiante</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Estudiante</th>
                        <th class="hidden px-3 py-3 md:table-cell sm:px-5">Evaluaciones</th>
                        <th class="hidden px-3 py-3 lg:table-cell sm:px-5">Tareas</th>
                        <th class="hidden px-3 py-3 lg:table-cell sm:px-5">Ejercicios</th>
                        <th class="px-3 py-3 sm:px-5">Promedio</th>
                        <th class="px-3 py-3 sm:px-5"><span class="sr-only">Ver</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($report['student_rows'] as $row)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-4 sm:px-5">
                            <p class="font-medium text-slate-900">{{ $row['name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $row['document'] }}</p>
                        </td>
                        <td class="hidden px-3 py-4 tabular-nums text-slate-700 md:table-cell sm:px-5">{{ number_format($row['evaluations'], 1) }}</td>
                        <td class="hidden px-3 py-4 tabular-nums text-slate-700 lg:table-cell sm:px-5">{{ number_format($row['homework'], 1) }}</td>
                        <td class="hidden px-3 py-4 tabular-nums text-slate-700 lg:table-cell sm:px-5">{{ number_format($row['class_exercises'], 1) }}</td>
                        <td class="px-3 py-4 sm:px-5">
                            <span class="font-semibold tabular-nums {{ $row['average'] >= 3.5 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($row['average'], 1) }}</span>
                            <p class="text-xs text-slate-500">{{ $row['status'] }}</p>
                        </td>
                        <td class="px-3 py-4 text-right sm:px-5">
                            <a href="{{ route('admin.presentation.reports.students.show', $row['id']) }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:underline">Ver notas</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @include('livewire.admin.presentation.reports.partials.email-modal')
</div>
