<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">{{ org_term('Negocios') }}</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.participants.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Participantes</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $participant->full_name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">{{ org_term('Negocios') }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $participant->full_name }}</h1>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $participant->status ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20' }}">
                        {{ $participant->status ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Detalle del participante.</p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.participants.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Volver</a>
                @if($can_edit)
                <a href="{{ route('admin.participants.form.edit', $participant) }}" wire:navigate class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">Editar</a>
                @endif
                @if($can_delete)
                <button
                    type="button"
                    wire:click="deleteRecord"
                    class="btn btn-danger btn-sm flex-1 justify-center sm:flex-none disabled:cursor-not-allowed disabled:opacity-50"
                    @disabled($has_dependencies)
                    title="{{ $has_dependencies ? 'No se puede eliminar: está siendo utilizado en otras referencias' : 'Eliminar participante' }}"
                >
                    Eliminar
                </button>
                @endif
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Información personal</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $participant->first_name ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Apellido</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $participant->last_name ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Rol</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $participant->participant_role?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Documento</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">
                        @if($participant->document_type || $participant->document_number)
                            {{ $participant->document_type?->label() ?? $participant->document_type?->value }}{{ $participant->document_number ? ': '.$participant->document_number : '' }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @if(auth()->user()->hasRole('superAdmin'))
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Negocio</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $participant->business?->name ?? '—' }}</dd>
                </div>
                @endif
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Contacto</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Teléfono</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $participant->phone_number ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Email</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $participant->email ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Ciudad</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $participant->city?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">País</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $participant->country?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Dirección</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $participant->address ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Registro</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $participant->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
            </dl>
        </section>
    </div>
</div>
