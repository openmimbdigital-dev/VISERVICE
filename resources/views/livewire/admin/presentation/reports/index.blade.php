<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Presentación</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Reportes</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Reportes</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Reportes académicos</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Indicadores de demostración por curso y por estudiante. Exporta el listado o entra a cada reporte de notas.</p>
            </div>
            @if($section === 'courses')
            <a href="{{ route('admin.presentation.reports.courses.pdf') }}" class="btn btn-outline-secondary w-full justify-center sm:w-auto">Exportar PDF</a>
            @else
            <a href="{{ route('admin.presentation.reports.students.pdf', $filter_course !== '' ? ['course' => $filter_course] : []) }}" class="btn btn-outline-secondary w-full justify-center sm:w-auto">Exportar PDF</a>
            @endif
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <button type="button" wire:click="setSection('courses')"
            class="rounded-2xl border bg-white p-4 text-left shadow-sm transition {{ $section === 'courses' ? 'border-transparent ring-2 ring-indigo-500' : 'border-slate-200/90 ring-1 ring-slate-900/[0.04] hover:border-slate-300' }}">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Sección</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">Reporte por curso</p>
            <p class="mt-1 text-sm text-slate-500">Promedio, aprobados y estudiantes en riesgo.</p>
        </button>
        <button type="button" wire:click="setSection('students')"
            class="rounded-2xl border bg-white p-4 text-left shadow-sm transition {{ $section === 'students' ? 'border-transparent ring-2 ring-indigo-500' : 'border-slate-200/90 ring-1 ring-slate-900/[0.04] hover:border-slate-300' }}">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Sección</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">Reporte por estudiantes</p>
            <p class="mt-1 text-sm text-slate-500">Notas por tipo de actividad y estado.</p>
        </button>
    </div>

    @if($section === 'courses')
    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Reporte por curso</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Curso</th>
                        <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Miss</th>
                        <th class="px-3 py-3 sm:px-5">Estudiantes</th>
                        <th class="px-3 py-3 sm:px-5">Promedio</th>
                        <th class="hidden px-3 py-3 md:table-cell sm:px-5">Aprobados</th>
                        <th class="hidden px-3 py-3 lg:table-cell sm:px-5">En riesgo</th>
                        <th class="px-3 py-3 sm:px-5"><span class="sr-only">Ver</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($course_reports as $row)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-4 sm:px-5">
                            <p class="font-medium text-slate-900">{{ $row['name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $row['code'] }} · {{ $row['grade'] }}{{ $row['section'] }}</p>
                        </td>
                        <td class="hidden px-3 py-4 text-slate-700 sm:table-cell sm:px-5">{{ $row['teacher'] }}</td>
                        <td class="px-3 py-4 tabular-nums text-slate-900 sm:px-5">{{ $row['students'] }}</td>
                        <td class="px-3 py-4 font-semibold tabular-nums {{ $row['average'] >= 3.5 ? 'text-emerald-600' : 'text-rose-600' }} sm:px-5">{{ number_format($row['average'], 1) }}</td>
                        <td class="hidden px-3 py-4 tabular-nums text-slate-700 md:table-cell sm:px-5">{{ $row['passed'] }}</td>
                        <td class="hidden px-3 py-4 tabular-nums text-slate-700 lg:table-cell sm:px-5">{{ $row['at_risk'] }}</td>
                        <td class="px-3 py-4 text-right sm:px-5">
                            <a href="{{ route('admin.presentation.reports.courses.show', $row['id']) }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @else
    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-semibold text-slate-800">Reporte por estudiantes</h2>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar nombre o documento"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <select wire:model.live="filter_course" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Todos los cursos</option>
                        @foreach($courses as $course)
                        <option value="{{ $course['id'] }}">{{ $course['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Estudiante</th>
                        <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Curso</th>
                        <th class="hidden px-3 py-3 md:table-cell sm:px-5">Evaluaciones</th>
                        <th class="hidden px-3 py-3 lg:table-cell sm:px-5">Tareas</th>
                        <th class="hidden px-3 py-3 lg:table-cell sm:px-5">Ejercicios</th>
                        <th class="px-3 py-3 sm:px-5">Promedio</th>
                        <th class="px-3 py-3 sm:px-5"><span class="sr-only">Ver</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($student_reports as $row)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-4 sm:px-5">
                            <p class="font-medium text-slate-900">{{ $row['name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $row['document'] }}</p>
                        </td>
                        <td class="hidden px-3 py-4 text-slate-700 sm:table-cell sm:px-5">{{ $row['course_name'] }}</td>
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
                    @empty
                    <tr>
                        <td colspan="7" class="px-3 py-10 text-center text-sm text-slate-500 sm:px-5">No hay estudiantes con esos filtros.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif
</div>
