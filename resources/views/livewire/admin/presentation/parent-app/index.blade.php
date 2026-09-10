<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Presentación</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">App de padres</span>
    </nav>

    <header class="mb-8">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Participación</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">App móvil para padres</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">Simula el celular del acudiente. Desde la app entra a Mi aula, el curso del hijo, la inscripción, los pagos y los eventos del colegio.</p>
        </div>
    </header>

    <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:gap-12">
        <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035]">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sesión de demostración</p>
                <p class="mt-2 text-base font-semibold text-slate-900">{{ $guardian['name'] }}</p>
                <p class="text-sm text-slate-600">Acudiente de {{ $child['name'] }} · {{ $child['grade'] }}{{ $child['section'] }} · {{ $child['shift'] }}</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach([
                    ['title' => 'Mi aula', 'desc' => 'Asignaturas y actividades del hijo.'],
                    ['title' => 'Curso', 'desc' => 'Grupo, miss y horario.'],
                    ['title' => 'Inscripción', 'desc' => 'Documentos y estado del proceso.'],
                    ['title' => 'Pagos', 'desc' => 'Pensión y otros cobros pendientes.'],
                    ['title' => 'Eventos', 'desc' => 'Reuniones, izadas y ferias.'],
                ] as $item)
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">{{ $item['title'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $item['desc'] }}</p>
                </div>
                @endforeach
            </div>
            <p class="text-xs text-slate-500">Toca las pestañas y tarjetas del teléfono. Los pagos quedan en esta sesión, igual que en Facturación.</p>
        </div>

        <div class="flex justify-center lg:sticky lg:top-6">
            @include('livewire.admin.presentation.parent-app.phone')
        </div>
    </div>
</div>
