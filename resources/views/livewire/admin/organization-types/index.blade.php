<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.businesses.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Negocios</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Tipos de organización</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Tipos de organización</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Clasificación detallada vinculada a cada tipo de negocio (Taller, Iglesia, Centro Educativo).</p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                @can('business_types.view')
                <a href="{{ route('admin.business-types.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Tipos de negocio</a>
                @endcan
                @can('organization_types.create')
                <x-ui.create-button wire:click="openCreate" class="w-full justify-center sm:w-auto">Nuevo tipo</x-ui.create-button>
                @endcan
            </div>
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035]">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035]">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Activos</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035]">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Con negocios</p>
            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['with_items'] }}</p>
        </div>
    </div>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row">
        <div class="relative min-w-0 flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar..."
                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
        </div>
        <select wire:model.live="filter_business_type"
            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm sm:w-auto">
            <option value="">Todos los tipos de negocio</option>
            @foreach($business_types as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filter_status"
            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm sm:w-auto">
            <option value="">Todos los estados</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
        </select>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado</h2>
        </div>

        @if($organization_types->isEmpty())
            <div class="px-4 py-16 text-center sm:px-5">
                <p class="text-sm font-medium text-slate-900">No hay tipos de organización</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-3 text-left sm:px-5">Nombre</th>
                            <th class="hidden px-3 py-3 text-left md:table-cell sm:px-5">Tipo de negocio</th>
                            <th class="hidden px-3 py-3 text-left lg:table-cell sm:px-5">Etiqueta</th>
                            <th class="hidden px-3 py-3 text-center sm:table-cell sm:px-5">Negocios</th>
                            <th class="px-3 py-3 text-center sm:px-5">Estado</th>
                            <th class="px-3 py-3 text-right sm:px-5">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($organization_types as $type)
                            <tr class="transition hover:bg-slate-50/80" wire:key="org-type-{{ $type->id }}">
                                <td class="px-3 py-4 sm:px-5">
                                    <p class="font-medium text-slate-900">{{ $type->name }}</p>
                                    <p class="mt-0.5 text-xs text-indigo-600 md:hidden">{{ $type->business_type?->name ?? '—' }}</p>
                                </td>
                                <td class="hidden px-3 py-4 md:table-cell sm:px-5">
                                    <span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">{{ $type->business_type?->name ?? '—' }}</span>
                                </td>
                                <td class="hidden px-3 py-4 lg:table-cell sm:px-5">
                                    <span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs text-slate-600">{{ $type->label }}</span>
                                </td>
                                <td class="hidden px-3 py-4 text-center sm:table-cell sm:px-5">{{ $type->businesses_count }}</td>
                                <td class="px-3 py-4 text-center sm:px-5">
                                    @if($type->active)
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">Activo</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 sm:px-5">
                                    <div class="flex flex-wrap justify-end gap-1">
                                        @can('organization_types.edit')
                                        <button type="button" wire:click="openEdit({{ $type->id }})" class="rounded-lg p-2 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Editar">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button type="button" wire:click="toggleActive({{ $type->id }})" class="rounded-lg p-2 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600" title="Activar/Desactivar">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        </button>
                                        @endcan
                                        @can('organization_types.delete')
                                        <button type="button" wire:click="delete({{ $type->id }})" @disabled($type->businesses_count > 0)
                                            class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-40" title="Eliminar">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($organization_types->hasPages())
                <div class="border-t border-slate-100 px-4 py-4 sm:px-5">{{ $organization_types->links() }}</div>
            @endif
        @endif
    </section>

    @if($showModal)
    <x-ui.modal centered maxWidth="md">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">{{ $form->isEditing() ? 'Editar tipo de organización' : 'Nuevo tipo de organización' }}</h3>
            <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo de negocio <span class="text-rose-500">*</span></label>
                    <select wire:model="form.business_type_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.business_type_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">— Seleccionar —</option>
                        @foreach($business_types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('form.business_type_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model.live="form.name"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.name') border-rose-400 bg-rose-50 @enderror">
                    @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                @if($label_preview)
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Etiqueta (automática)</label>
                        <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-sm text-slate-600">{{ $label_preview }}</p>
                    </div>
                @endif

                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-slate-900">Estado</p>
                        <p class="text-xs text-slate-500">{{ $form->active ? 'Activo' : 'Inactivo' }}</p>
                    </div>
                    <button type="button" wire:click="$toggle('form.active')"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out {{ $form->active ? 'bg-indigo-600' : 'bg-slate-300' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out {{ $form->active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closeModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">Guardar</button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
