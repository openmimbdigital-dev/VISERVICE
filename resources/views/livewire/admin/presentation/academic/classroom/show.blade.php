<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.academic.classroom.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Mi aula</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $subject['name'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Académico</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $subject['name'] }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">{{ $subject['teacher'] }} · {{ $subject['shift_label'] }} · Sección {{ $subject['section'] }}. Entra a un tipo para crear la actividad.</p>
            </div>
            <a href="{{ route('admin.presentation.academic.classroom.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Volver</a>
        </div>
    </header>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach([
            ['key' => 'evaluation', 'desc' => 'Parciales, quizzes y pruebas.'],
            ['key' => 'homework', 'desc' => 'Talleres y trabajos para la casa.'],
            ['key' => 'class_exercise', 'desc' => 'Ejercicios y laboratorios en clase.'],
        ] as $card)
        <a href="{{ route('admin.presentation.academic.classroom.activities.index', [$subject['id'], $card['key']]) }}" wire:navigate
            class="group overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035] transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">{{ $types[$card['key']] }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-slate-900">{{ $counts[$card['key']] }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ $card['desc'] }}</p>
            <p class="mt-4 text-sm font-semibold text-indigo-600 group-hover:underline">Entrar y crear</p>
        </a>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Todas las actividades</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Actividad</th>
                        <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Tipo</th>
                        <th class="hidden px-3 py-3 md:table-cell sm:px-5">Entrega</th>
                        <th class="px-3 py-3 sm:px-5"><span class="sr-only">Resolver</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activities as $activity)
                    @php
                        $badge = match ($activity['type']) {
                            'evaluation' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                            'homework' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                            default => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-4 sm:px-5">
                            <p class="font-medium text-slate-900">{{ $activity['title'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $activity['description'] }}</p>
                        </td>
                        <td class="hidden px-3 py-4 sm:table-cell sm:px-5">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $activity['type_label'] }}</span>
                        </td>
                        <td class="hidden px-3 py-4 text-slate-600 md:table-cell sm:px-5">{{ \Carbon\Carbon::parse($activity['due_date'])->format('d/m/Y') }}</td>
                        <td class="px-3 py-4 text-right sm:px-5">
                            <a href="{{ route('admin.presentation.academic.classroom.activities.solve', [$subject['id'], $activity['type'], $activity['id']]) }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:underline">
                                {{ $activity['submitted'] ? 'Ver' : 'Ingresar' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-10 text-center text-sm text-slate-500 sm:px-5">Aún no hay actividades. Entra a un tipo para crear la primera.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
