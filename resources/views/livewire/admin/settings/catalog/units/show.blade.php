<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.catalog-products.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Configuración</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.catalog-products.units.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Unidades</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $unit->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Configuración · Productos</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $unit->name }}</h1>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-500/20">{{ $unit->symbol }}</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $unit->active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20' }}">
                        {{ $unit->active ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.settings.catalog-products.units.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Volver</a>
                @if($can_edit)
                <a href="{{ route('admin.settings.catalog-products.units.index', ['edit' => $unit->id]) }}" wire:navigate class="btn btn-primary btn-sm flex-1 sm:flex-none justify-center">Editar</a>
                @endif
                @can('settings.units.delete')
                <button type="button" wire:click="deleteRecord" @disabled(! $can_delete)
                    class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center disabled:cursor-not-allowed disabled:opacity-50">Eliminar</button>
                @endcan
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Información general</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $unit->name }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Símbolo</dt>
                    <dd class="font-mono text-sm text-slate-700 sm:col-span-2">{{ $unit->symbol }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">General</dt>
                    <dd class="sm:col-span-2">{{ $unit->general ? 'Sí' : 'No' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Negocio</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $unit->business?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Registrada</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $unit->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Uso en el sistema</h2>
            </div>
            <div class="px-5 py-5">
                <p class="text-3xl font-bold text-slate-900">{{ $items_count }}</p>
                <p class="mt-1 text-sm text-slate-600">Producto(s) asociado(s)</p>
                @if($is_general_readonly)
                <p class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50 px-3.5 py-2.5 text-xs text-indigo-800">
                    Unidad general del sistema. Los negocios pueden consultarla pero no editarla ni eliminarla.
                </p>
                @elseif($items_count > 0)
                <p class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                    Esta unidad está en uso y no puede eliminarse.
                </p>
                @endif
            </div>
        </section>
    </div>
</div>
