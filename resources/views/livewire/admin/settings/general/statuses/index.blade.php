<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.general.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Configuración</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.general.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">General</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $config['title'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Configuración · General</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $config['title'] }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">{{ $config['description'] }}</p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.settings.general.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Volver</a>
                @can('settings.statuses.create')
                <x-ui.create-button wire:click="openCreate" size="sm" class="flex-1 sm:flex-none justify-center">
                    {{ $config['create_button_text'] ?? 'Nuevo estado' }}
                </x-ui.create-button>
                @endcan
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado de estados</h2>
            <div class="w-full sm:max-w-xs">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o etiqueta…"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/40">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-5">Nombre</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-5">Etiqueta</th>
                        <th class="hidden px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 md:table-cell sm:px-5">Módulos</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold uppercase text-slate-500 sm:px-5">Activo</th>
                        <th class="px-3 py-2 sm:px-5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($statuses as $status)
                    <tr wire:key="status-{{ $status->id }}">
                        <td class="px-3 py-3 font-mono text-sm text-slate-800 sm:px-5">{{ $status->name }}</td>
                        <td class="px-3 py-3 text-sm text-slate-900 sm:px-5">{{ $status->label }}</td>
                        <td class="hidden px-3 py-3 md:table-cell sm:px-5">
                            <div class="flex flex-wrap gap-1">
                                @foreach((array) $status->type as $module)
                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-700">
                                    {{ $module_options[$module] ?? $module }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center sm:px-5">
                            @if($status->active)
                            <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Sí</span>
                            @else
                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">No</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 sm:px-5">
                            @php $is_in_use = isset($statuses_in_use[$status->name]); @endphp
                            <div class="flex flex-wrap justify-end gap-1">
                                @can('settings.statuses.edit')
                                <button type="button"
                                    @if($is_in_use) disabled @else wire:click="openEdit({{ $status->id }})" @endif
                                    class="rounded p-1 text-slate-400 transition {{ $is_in_use ? 'cursor-not-allowed opacity-40' : 'hover:text-indigo-600' }}"
                                    title="{{ $is_in_use ? 'No se puede editar: estado en uso' : 'Editar' }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                @endcan
                                @can('settings.statuses.delete')
                                <button type="button"
                                    @if($is_in_use) disabled @else wire:click="confirmDelete({{ $status->id }})" @endif
                                    class="rounded p-1 text-slate-400 transition {{ $is_in_use ? 'cursor-not-allowed opacity-40' : 'hover:text-red-600' }}"
                                    title="{{ $is_in_use ? 'No se puede eliminar: estado en uso' : 'Eliminar' }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">No hay estados registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($statuses->hasPages())
        <div class="border-t border-slate-100 px-4 py-3 sm:px-5">
            {{ $statuses->links() }}
        </div>
        @endif
    </section>

    @if($showModal)
    <x-ui.modal centered maxWidth="lg">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">
                {{ $form->isEditing() ? 'Editar estado' : 'Nuevo estado' }}
            </h3>
            <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre técnico <span class="text-rose-500">*</span></label>
                        <input wire:model="form.name" type="text" placeholder="ej. in_progress"
                            @disabled($form->isEditing())
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-60 @error('form.name') border-rose-400 bg-rose-50 @enderror">
                        @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        @if($form->isEditing())
                        <p class="mt-1 text-xs text-slate-400">El nombre técnico no se puede cambiar porque se usa como referencia.</p>
                        @endif
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Etiqueta <span class="text-rose-500">*</span></label>
                        <input wire:model="form.label" type="text" placeholder="Ej. En proceso"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.label') border-rose-400 bg-rose-50 @enderror">
                        @error('form.label') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Módulos <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach($module_options as $module_key => $module_label)
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">
                            <input type="checkbox" wire:model="form.type" value="{{ $module_key }}"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                            {{ $module_label }}
                        </label>
                        @endforeach
                    </div>
                    @error('form.type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    @error('form.type.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="$toggle('form.active')"
                            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200 {{ $form->active ? 'bg-indigo-600' : 'bg-slate-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $form->active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                        <span class="text-sm {{ $form->active ? 'font-medium text-emerald-700' : 'text-slate-500' }}">
                            {{ $form->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closeModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                    <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Guardar cambios' : 'Crear estado' }}</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
