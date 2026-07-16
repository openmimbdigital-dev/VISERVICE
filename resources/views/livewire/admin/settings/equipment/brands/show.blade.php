<div class="relative mx-auto w-full max-w-[90rem]">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.equipment.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Configuración</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.equipment.brands') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Marcas</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $brand->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Configuración · Equipos</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $brand->name }}</h1>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $brand->active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20' }}">
                        {{ $brand->active ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.settings.equipment.brands') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
                @can('settings.brands.delete')
                <button type="button" wire:click="deleteRecord"
                    @disabled(! $can_delete)
                    title="{{ $is_general_readonly ? 'Marca general del sistema: no se puede eliminar' : ($can_delete ? 'Eliminar marca' : 'No se puede eliminar: tiene equipos asociados') }}"
                    class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar
                </button>
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
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $brand->name }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Etiqueta</dt>
                    <dd class="font-mono text-sm lowercase text-slate-700 sm:col-span-2">{{ $brand->label }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">General</dt>
                    <dd class="sm:col-span-2">
                        @if($brand->general)
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>
                        @else
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>
                        @endif
                    </dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Tipos de equipo</dt>
                    <dd class="sm:col-span-2">
                        @if($brand->equipmentTypes->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($brand->equipmentTypes as $equipment_type)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-500/20">
                                        {{ $equipment_type->name }}
                                        @if(! $equipment_type->active)
                                            <span class="ml-1 text-slate-400">(inactivo)</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-sm text-slate-500">Sin tipos asociados</span>
                        @endif
                    </dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Negocio</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $brand->business?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Registrada</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $brand->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Uso en el sistema</h2>
            </div>
            <div class="px-5 py-5">
                <p class="text-3xl font-bold text-slate-900">{{ $equipment_count }}</p>
                <p class="mt-1 text-sm text-slate-600">Equipo(s) asociado(s)</p>
                @if($is_general_readonly)
                <p class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50 px-3.5 py-2.5 text-xs text-indigo-800">
                    Marca general del sistema. Los negocios pueden consultarla pero no editarla ni eliminarla.
                </p>
                @elseif($equipment_count > 0)
                <p class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                    Esta marca está en uso y no puede eliminarse hasta que no tenga equipos asociados.
                </p>
                @else
                <p class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 px-3.5 py-2.5 text-xs text-emerald-800">
                    Sin dependencias. Puede eliminarse si tienes permiso de edición.
                </p>
                @endif
            </div>
        </section>
    </div>
</div>
