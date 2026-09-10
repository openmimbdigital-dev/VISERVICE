<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Presentación</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.academic.courses.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Cursos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $course['name'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Académico</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $course['name'] }}</h1>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">{{ $course['code'] }}</span>
                </div>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Estudiantes inscritos en este curso de demostración.</p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.presentation.academic.courses.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">
                    Volver
                </a>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Información del curso</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Grado</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $course['grade'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Jornada</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $course['shift_label'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Sección</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $course['section'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Miss</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $course['teacher'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Horario</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $course['schedule'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Aula</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $course['room'] }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Resumen</h2>
            </div>
            <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Estudiantes</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums text-indigo-600">{{ count($course['students']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Grupo</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $course['grade'] }}{{ $course['section'] }}</p>
                </div>
            </div>
        </section>
    </div>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Estudiantes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Nombre</th>
                        <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Documento</th>
                        <th class="hidden px-3 py-3 md:table-cell sm:px-5">Correo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($course['students'] as $student)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-4 font-medium text-slate-900 sm:px-5">
                            {{ $student['name'] }}
                            <p class="mt-0.5 font-normal text-xs text-slate-500 sm:hidden">{{ $student['document'] }}</p>
                        </td>
                        <td class="hidden px-3 py-4 font-mono text-slate-600 sm:table-cell sm:px-5">{{ $student['document'] }}</td>
                        <td class="hidden px-3 py-4 text-slate-600 md:table-cell sm:px-5">{{ $student['email'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-3 py-10 text-center text-sm text-slate-500 sm:px-5">Este curso no tiene estudiantes de demostración.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
