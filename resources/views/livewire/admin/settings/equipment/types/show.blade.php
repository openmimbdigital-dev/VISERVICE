<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.equipment.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Configuración</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.equipment.types') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Tipos de equipo</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $equipment_type->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Configuración · Equipos</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $equipment_type->name }}</h1>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $equipment_type->active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20' }}">
                        {{ $equipment_type->active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.settings.equipment.types') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
                @can('settings.equipment_types.delete')
                <button type="button" wire:click="deleteRecord"
                    @disabled(! $can_delete)
                    title="{{ $can_delete ? 'Eliminar tipo' : 'No se puede eliminar: tiene equipos asociados' }}"
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
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $equipment_type->name }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Etiqueta</dt>
                    <dd class="font-mono text-sm lowercase text-slate-700 sm:col-span-2">{{ $equipment_type->label }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">General</dt>
                    <dd class="sm:col-span-2">
                        @if($equipment_type->general)
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>
                        @else
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>
                        @endif
                    </dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Negocios</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">
                        @if($equipment_type->businesses->isNotEmpty())
                            {{ $equipment_type->businesses->pluck('name')->join(', ') }}
                        @elseif($equipment_type->business)
                            {{ $equipment_type->business->name }}
                        @else
                            <span class="text-slate-500">Todos (sin restricción por negocio)</span>
                        @endif
                    </dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Registrado</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $equipment_type->created_at?->format('d/m/Y H:i') }}</dd>
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
                @if($equipment_count > 0)
                <p class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                    Este tipo está en uso y no puede eliminarse hasta que no tenga equipos asociados.
                </p>
                @else
                <p class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 px-3.5 py-2.5 text-xs text-emerald-800">
                    Sin dependencias. Puede eliminarse si tienes permiso.
                </p>
                @endif
            </div>
        </section>
    </div>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Atributos asociados</h2>
            <p class="mt-1 text-sm text-slate-600">Campos personalizados disponibles para equipos de este tipo.</p>
        </div>
        <div class="overflow-x-auto">
            @if($linked_attributes->isNotEmpty())
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-5">Nombre</th>
                        <th scope="col" class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:table-cell sm:px-5">Tipo</th>
                        <th scope="col" class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell md:px-5">Alcance</th>
                        <th scope="col" class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 lg:table-cell lg:px-5">Obligatorio</th>
                        <th scope="col" class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 lg:table-cell lg:px-5">Oculto en creación</th>
                        @if($can_view_attributes)
                        <th scope="col" class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-5"><span class="sr-only">Ver</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($linked_attributes as $attribute)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-3 py-4 sm:px-5">
                            <p class="text-sm font-medium text-slate-900">{{ $attribute->name }}</p>
                            <p class="mt-0.5 text-xs text-slate-500 sm:hidden">{{ $attribute->typeLabel() }}</p>
                        </td>
                        <td class="hidden px-3 py-4 text-sm text-slate-700 sm:table-cell sm:px-5">{{ $attribute->typeLabel() }}</td>
                        <td class="hidden px-3 py-4 md:table-cell md:px-5">
                            @if($attribute->general)
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">General</span>
                            @else
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">Por comercio</span>
                            @endif
                        </td>
                        <td class="hidden px-3 py-4 text-sm text-slate-700 lg:table-cell lg:px-5">{{ $attribute->required ? 'Sí' : 'No' }}</td>
                        <td class="hidden px-3 py-4 text-sm text-slate-700 lg:table-cell lg:px-5">{{ $attribute->nullable_creation ? 'Sí' : 'No' }}</td>
                        @if($can_view_attributes)
                        <td class="px-3 py-4 text-right sm:px-5">
                            <a href="{{ route('admin.settings.equipment.attributes.show', $attribute) }}" wire:navigate
                                class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50">
                                <span class="hidden sm:inline">Ver detalle</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="px-5 py-8 text-center text-sm text-slate-500">Sin atributos asociados a este tipo de equipo.</p>
            @endif
        </div>
    </section>
</div>
