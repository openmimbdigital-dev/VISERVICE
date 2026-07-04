<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.businesses.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Negocios</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Cargos del equipo</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Cargos del equipo</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Catálogo de cargos por tipo de negocio. Los registros generales aplican a todos los negocios del tipo y solo el superAdmin puede modificarlos.
                </p>
            </div>
            @can('team_positions.create')
            <x-ui.create-button wire:click="openCreate" class="w-full justify-center sm:w-auto">
                Nuevo cargo
            </x-ui.create-button>
            @endcan
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/90 bg-white px-4 py-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <p class="text-xs font-medium text-slate-500">Total</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white px-4 py-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <p class="text-xs font-medium text-slate-500">Activos</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $stats['active'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white px-4 py-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <p class="text-xs font-medium text-slate-500">Generales</p>
            <p class="mt-1 text-2xl font-bold text-indigo-700">{{ $stats['general'] }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado de cargos</h2>
        </div>
        <div class="overflow-x-auto p-3 sm:p-4">
            <livewire:admin.team-positions.datatable-team-positions :key="'team-positions-datatable'" />
        </div>
    </section>

    @if($showModal)
    <x-ui.modal centered>
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">
                {{ $form->isEditing() ? 'Editar cargo' : 'Nuevo cargo' }}
            </h3>
            <button wire:click="closeModal" type="button"
                class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                @if($is_super_admin)
                <p class="rounded-xl border border-indigo-100 bg-indigo-50 px-3.5 py-2.5 text-xs text-indigo-800">
                    Los cargos creados como superAdmin son <strong>generales</strong> y aplican a todos los negocios del tipo seleccionado.
                </p>
                @else
                <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-600">
                    El cargo quedará asociado a tu negocio.
                </p>
                @endif

                @if($is_super_admin)
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
                @endif

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
                    <input wire:model="form.name" type="text" placeholder="Ej. Mecánico General"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.name') border-rose-400 bg-rose-50 @enderror">
                    @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
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
                <button type="button" wire:click="closeModal"
                    class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">
                    Cancelar
                </button>
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                    {{ $form->isEditing() ? 'Guardar cambios' : 'Crear cargo' }}
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
