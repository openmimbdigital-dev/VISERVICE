<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">{{ org_term('Negocios') }}</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Participantes</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">{{ org_term('Negocios') }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Participantes</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Directorio de participantes del negocio. Gestiona datos de contacto, documento y rol.</p>
            </div>
            <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row">
                @can('participants.roles.view')
                <a href="{{ route('admin.participants.roles.index') }}" wire:navigate
                    class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">
                    Roles
                </a>
                @endcan
                @can('participants.view')
                <a href="{{ route('admin.participants.public-registration-link') }}" wire:navigate
                    class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">
                    Enlace público
                </a>
                @endcan
                @can('participants.create')
                <x-ui.create-button :href="route('admin.participants.form')" class="w-full justify-center sm:w-auto">
                    Nuevo participante
                </x-ui.create-button>
                @endcan
            </div>
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:max-w-md">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Activos</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-emerald-600">{{ $stats['active'] }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado de participantes</h2>
        </div>
        <div class="overflow-x-auto p-3 sm:p-4">
            <livewire:dynamic-component component="admin.participants.datatable-participants" :key="'participants-datatable'" />
        </div>
    </section>
</div>
