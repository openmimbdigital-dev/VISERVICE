<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.equipment.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Equipos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $equipment_type->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller · Equipos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $equipment_type->name }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Equipos registrados de este tipo en el taller.</p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.workshop.equipment.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
                @can('workshop.equipment.create')
                <x-ui.create-button wire:click="openCreate" size="sm" class="flex-1 sm:flex-none justify-center">
                    Nuevo equipo
                </x-ui.create-button>
                @endcan
            </div>
        </div>
        <div class="mt-6 grid w-full grid-cols-2 gap-3 sm:max-w-md">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-slate-900 sm:text-3xl">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Activos</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-emerald-600 sm:text-3xl">{{ $stats['active'] }}</p>
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado de equipos</h2>
        </div>
        <div class="overflow-x-auto p-3 sm:p-4">
            <livewire:admin.workshop.equipment.datatable-equipment
                :equipment_type_id="$equipment_type->id"
                :key="'equipment-datatable-' . $equipment_type->id"
            />
        </div>
    </section>

    <x-ui.modal x-show="$wire.showModal" x-cloak style="display:none">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">{{ $editing_id ? 'Editar equipo' : 'Nuevo equipo' }}</h3>
            <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label-up">Cliente *</label>
                    <select wire:model.live="client_id" class="form-select">
                        <option value="">Seleccionar cliente</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label-up">Placa *</label>
                    <input type="text" wire:model="plate" class="form-input uppercase" />
                    @error('plate')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label-up">Marca</label>
                    <input type="text" wire:model="brand" class="form-input" />
                </div>
                <div>
                    <label class="label-up">Modelo</label>
                    <input type="text" wire:model="model" class="form-input" />
                </div>
                <div>
                    <label class="label-up">Año</label>
                    <input type="number" wire:model="year" class="form-input" min="1900" max="{{ date('Y') + 1 }}" />
                </div>
                <div>
                    <label class="label-up">Km actual</label>
                    <input type="number" wire:model="km_current" class="form-input" min="0" />
                </div>
                <div class="sm:col-span-2">
                    <label class="label-up">Notas</label>
                    <textarea wire:model="notes" class="form-input" rows="2"></textarea>
                </div>
            </div>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <button type="button" wire:click="closeModal" class="btn btn-outline-secondary w-full justify-center sm:w-auto">Cancelar</button>
            <button type="button" wire:click="save" wire:loading.attr="disabled" class="btn btn-primary w-full justify-center sm:w-auto">Guardar</button>
        </div>
    </x-ui.modal>
</div>
