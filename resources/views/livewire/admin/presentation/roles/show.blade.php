<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.roles.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Roles</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $role_record['name'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Acceso</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $role_record['name'] }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">{{ $role_record['description'] }}</p>
            </div>
            <a href="{{ route('admin.presentation.roles.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Volver</a>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Permisos</h2>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($role_record['permissions'] as $permission)
            <li class="px-5 py-3 text-sm text-slate-800">{{ $permission }}</li>
            @empty
            <li class="px-5 py-8 text-center text-sm text-slate-500">Este rol no tiene permisos asignados.</li>
            @endforelse
        </ul>
    </section>
</div>
