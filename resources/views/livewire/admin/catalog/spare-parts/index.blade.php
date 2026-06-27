<div class="relative mx-auto w-full max-w-[90rem]">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex items-center gap-x-2 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Catálogo</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Repuestos</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Catálogo</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Repuestos</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Inventario de repuestos y autopartes. Los ítems con stock bajo se resaltan automáticamente.</p>
            </div>
            <div class="grid shrink-0 grid-cols-2 gap-3 sm:max-w-xs">
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Stock bajo</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums {{ $stats['low_stock'] > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $stats['low_stock'] }}</p>
                </div>
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Catálogo de repuestos</h2>
            <button wire:click="openCreate" class="btn btn-primary btn-sm">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo repuesto
            </button>
        </div>
        <div class="p-4">
            <livewire:admin.catalog.spare-parts.datatable-spare-parts />
        </div>
    </section>

    {{-- Modal crear/editar --}}
    <x-ui.modal x-show="$wire.showModal" x-cloak style="display:none">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">{{ $editing_id ? 'Editar repuesto' : 'Nuevo repuesto' }}</h3>
            <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label-up">Nombre *</label>
                    <input type="text" wire:model="name" class="form-input" />
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div><label class="label-up">Código</label><input type="text" wire:model="code" class="form-input" /></div>
                <div><label class="label-up">Marca</label><input type="text" wire:model="brand" class="form-input" /></div>
                <div>
                    <label class="label-up">Categoría</label>
                    <input type="text" wire:model="category" class="form-input" list="sp-cats" />
                    <datalist id="sp-cats">
                        @foreach($categories as $cat)<option value="{{ $cat }}">@endforeach
                    </datalist>
                </div>
                <div>
                    <label class="label-up">Unidad</label>
                    <select wire:model="unit" class="form-select">
                        @foreach(['und','kit','lt','ml','kg','g','m','par','caja','rollo'] as $u)
                            <option value="{{ $u }}">{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-up">Precio unit. *</label>
                    <input type="number" wire:model="unit_price" class="form-input" min="0" step="100" />
                    @error('unit_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div><label class="label-up">Stock actual</label><input type="number" wire:model="stock" class="form-input" min="0" /></div>
                <div><label class="label-up">Stock mínimo</label><input type="number" wire:model="min_stock" class="form-input" min="0" /></div>
                <div class="sm:col-span-2">
                    <label class="label-up">Descripción</label>
                    <textarea wire:model="description" class="form-input" rows="2"></textarea>
                </div>
            </div>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <button type="button" wire:click="closeModal" class="btn btn-outline-secondary w-full justify-center sm:w-auto">Cancelar</button>
            <button type="button" wire:click="save" wire:loading.attr="disabled" class="btn btn-primary w-full justify-center sm:w-auto">Guardar</button>
        </div>
    </x-ui.modal>
</div>
