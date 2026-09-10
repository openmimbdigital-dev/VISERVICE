<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.reports.index', ['section' => 'students']) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Reportes</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $report['name'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Reportes</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $report['name'] }}</h1>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $report['average'] >= 3.5 ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20' }}">{{ $report['status'] }}</span>
                </div>
                <p class="mt-2 max-w-xl text-sm text-slate-600">{{ $report['period'] }} · {{ $report['course_name'] }} · Miss {{ $report['teacher'] }}</p>
            </div>
            <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row">
                <a href="{{ route('admin.presentation.reports.index', ['section' => 'students']) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Volver</a>
                <a href="{{ route('admin.presentation.reports.students.pdf.show', $report['id']) }}" class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Exportar PDF</a>
                <button type="button" wire:click="openEmailModal" class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">Enviar por correo</button>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Estudiante</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $report['name'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Documento</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $report['document'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Correo</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $report['email'] }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Curso</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Curso</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $report['course_name'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Grupo</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $report['grade'] }}{{ $report['section'] }} · {{ $report['shift_label'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Promedio</dt>
                    <dd class="text-sm font-semibold tabular-nums {{ $report['average'] >= 3.5 ? 'text-emerald-600' : 'text-rose-600' }} sm:col-span-2">{{ number_format($report['average'], 1) }}</dd>
                </div>
            </dl>
        </section>
    </div>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Detalle de notas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Actividad</th>
                        <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Tipo</th>
                        <th class="px-3 py-3 sm:px-5">Nota</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($report['items'] as $item)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-4 font-medium text-slate-900 sm:px-5">
                            {{ $item['name'] }}
                            <p class="mt-0.5 text-xs font-normal text-slate-500 sm:hidden">{{ $item['type_label'] }}</p>
                        </td>
                        <td class="hidden px-3 py-4 text-slate-600 sm:table-cell sm:px-5">{{ $item['type_label'] }}</td>
                        <td class="px-3 py-4 font-semibold tabular-nums text-slate-900 sm:px-5">{{ number_format($item['score'], 1) }} / {{ number_format($item['max_score'], 1) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="grid grid-cols-1 gap-3 border-t border-slate-100 bg-slate-50/60 px-4 py-4 sm:grid-cols-3 sm:px-5">
            <p class="text-sm text-slate-600">Evaluaciones <span class="font-semibold tabular-nums text-slate-900">{{ number_format($report['evaluations'], 1) }}</span></p>
            <p class="text-sm text-slate-600">Tareas <span class="font-semibold tabular-nums text-slate-900">{{ number_format($report['homework'], 1) }}</span></p>
            <p class="text-sm text-slate-600">Ejercicios <span class="font-semibold tabular-nums text-slate-900">{{ number_format($report['class_exercises'], 1) }}</span></p>
        </div>
    </section>

    @include('livewire.admin.presentation.reports.partials.email-modal')
</div>
