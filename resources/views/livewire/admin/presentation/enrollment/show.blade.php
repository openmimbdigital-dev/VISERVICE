<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.enrollment.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inscripción</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $enrollment['child_name'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Inscripción</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $enrollment['child_name'] }}</h1>
                    @php
                        $badge = match ($enrollment['status']) {
                            'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                            'pending_payment' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                            default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        };
                    @endphp
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $enrollment['status_label'] }}</span>
                </div>
            </div>
            <a href="{{ route('admin.presentation.enrollment.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Volver</a>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Acudiente</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $enrollment['parent_name'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Documento</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $enrollment['parent_document'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Correo</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $enrollment['parent_email'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Teléfono</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $enrollment['parent_phone'] }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Estudiante</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $enrollment['child_name'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Documento</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $enrollment['child_document'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Grado</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $enrollment['grade'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Jornada</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $enrollment['shift_label'] }}</dd>
                </div>
            </dl>
        </section>
    </div>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Documentación</h2>
        </div>
        <ul class="divide-y divide-slate-100">
            @foreach($enrollment['documents'] as $document)
            <li class="flex flex-col gap-1 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-900">{{ $document['label'] }}</p>
                    <p class="text-xs text-slate-500">{{ $document['uploaded'] ? $document['name'] : 'Sin archivo' }}</p>
                </div>
                @if($document['uploaded'])
                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">Cargado</span>
                @else
                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20">Pendiente</span>
                @endif
            </li>
            @endforeach
        </ul>
    </section>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Pago</h2>
        </div>
        <dl class="divide-y divide-slate-100 px-5 py-2">
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Valor</dt>
                <dd class="text-sm font-semibold text-slate-900 sm:col-span-2">${{ number_format((int) $enrollment['payment_amount'], 0, ',', '.') }}</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Medio</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $enrollment['payment_method_label'] }}</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Referencia</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $enrollment['payment_reference'] !== '' ? $enrollment['payment_reference'] : 'Pendiente' }}</dd>
            </div>
        </dl>
    </section>
</div>
