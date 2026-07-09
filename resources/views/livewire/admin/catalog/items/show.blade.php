<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Catálogo</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.catalog.items.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Productos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $item->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Catálogo</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $item->name }}</h1>
                    <span class="font-mono text-xs text-slate-500">{{ $item->code }}</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $item->status ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20' }}">
                        {{ $item->status ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.catalog.items.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Volver</a>
                @if($can_edit)
                <a href="{{ route('admin.catalog.items.edit', $item) }}" wire:navigate class="btn btn-primary btn-sm flex-1 sm:flex-none justify-center">Editar</a>
                @endif
                @if($can_delete)
                <button type="button" wire:click="deleteRecord" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">Eliminar</button>
                @endif
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
                    <dt class="text-xs font-medium text-slate-500">Código</dt>
                    <dd class="font-mono text-sm text-slate-900 sm:col-span-2">{{ $item->code }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $item->name }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Descripción</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $item->description ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Comercio</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $item->business?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Registrado</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $item->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Clasificación y precios</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Tipo</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $item->item_type?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Categoría</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $item->item_category?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Unidad</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $item->unit ? $item->unit->name . ' (' . $item->unit->symbol . ')' : '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Marca</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $item->brand?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Precio costo</dt>
                    <dd class="tabular-nums text-sm text-slate-900 sm:col-span-2">$ {{ number_format((float) $item->cost_price, 2, ',', '.') }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Precio venta</dt>
                    <dd class="tabular-nums text-sm font-medium text-slate-900 sm:col-span-2">$ {{ number_format((float) $item->sale_price, 2, ',', '.') }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Inventario</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">
                        {{ $item->track_inventory ? 'Sí' : 'No' }}
                        <span class="text-xs text-slate-500">(según categoría: {{ $item->item_category?->inventory ? 'cuantificable' : 'no cuantificable' }})</span>
                    </dd>
                </div>
            </dl>
        </section>
    </div>
</div>
