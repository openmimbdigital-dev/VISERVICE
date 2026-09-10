<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Presentación</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Cursos</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Académico</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Cursos</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Listado de cursos de demostración. Entra a un curso para ver sus estudiantes.</p>
            </div>
        </div>
    </header>

    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Cursos</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-slate-900 sm:text-3xl">{{ count($courses) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Estudiantes</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-indigo-600 sm:text-3xl">{{ collect($courses)->sum('students_count') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($courses as $course)
        <a href="{{ route('admin.presentation.academic.courses.show', $course['id']) }}" wire:navigate
            class="group overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035] transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-mono text-[11px] font-semibold uppercase tracking-wider text-indigo-600">{{ $course['code'] }}</p>
                        <h2 class="mt-1 truncate text-base font-semibold text-slate-900">{{ $course['name'] }}</h2>
                    </div>
                    <span class="shrink-0 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">
                        {{ $course['grade'] }} {{ $course['section'] }}
                    </span>
                </div>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-1">
                <div class="grid grid-cols-3 gap-2 py-3">
                    <dt class="text-xs font-medium text-slate-500">Jornada</dt>
                    <dd class="col-span-2 text-sm text-slate-900">{{ $course['shift_label'] }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2 py-3">
                    <dt class="text-xs font-medium text-slate-500">Miss</dt>
                    <dd class="col-span-2 text-sm text-slate-900">{{ $course['teacher'] }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2 py-3">
                    <dt class="text-xs font-medium text-slate-500">Horario</dt>
                    <dd class="col-span-2 text-sm text-slate-700">{{ $course['schedule'] }}</dd>
                </div>
            </dl>
            <div class="flex items-center justify-between border-t border-slate-100 px-5 py-3">
                <p class="text-xs text-slate-500">{{ $course['students_count'] }} {{ $course['students_count'] === 1 ? 'estudiante' : 'estudiantes' }}</p>
                <span class="text-xs font-semibold text-indigo-600 group-hover:underline">Ver estudiantes</span>
            </div>
        </a>
        @endforeach
    </div>
</div>
