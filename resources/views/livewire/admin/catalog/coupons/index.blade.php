<div class="relative mx-auto w-full max-w-[90rem]">
    <nav class="mb-6 flex items-center gap-x-2 text-xs font-medium text-slate-400">
        <a href="{{ route('dashboard') }}" wire:navigate class="transition hover:text-indigo-600">Inicio</a>
        <span>/</span>
        <a href="{{ route('admin.catalog.products.index') }}" wire:navigate class="transition hover:text-indigo-600">Catálogo</a>
        <span>/</span>
        <span class="text-slate-600">Cupones</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Catálogo</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Cupones de descuento</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Descuentos que se aplican al total de una orden de trabajo escribiendo su código.
                    Para descontar un producto puntual, usa el descuento del producto en el catálogo.
                </p>
            </div>
            <div class="grid shrink-0 grid-cols-2 gap-3 sm:max-w-xs">
                <div class="rounded-2xl border border-slate-200/90 bg-white px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Cupones</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-emerald-700">Activos</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Listado</h2>
            @if($can_create)
            <button type="button" wire:click="openCreate" class="btn btn-primary btn-sm">Nuevo cupón</button>
            @endif
        </div>
        <div class="p-4">
            <livewire:admin.catalog.coupons.datatable-coupons />
        </div>
    </section>

    @if($showModal)
    <x-ui.modal centered maxWidth="xl">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">
                {{ $form->isEditing() ? 'Editar cupón' : 'Nuevo cupón' }}
            </h3>
            <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-5 overflow-y-auto px-4 py-5 sm:px-6">
                @if($is_super_admin)
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Negocio <span class="text-rose-500">*</span></label>
                    <select wire:model="form.business_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.business_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar negocio</option>
                        @foreach($businesses as $business)
                        <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                    @error('form.business_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Código <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="form.code" placeholder="BIENVENIDA10"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-sm uppercase placeholder:font-sans placeholder:normal-case @error('form.code') border-rose-400 bg-rose-50 @enderror">
                        <p class="mt-1 text-xs text-slate-500">Es lo que se escribe en la OT. Se guarda en mayúsculas.</p>
                        @error('form.code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre</label>
                        <input type="text" wire:model="form.name" placeholder="Promoción de bienvenida"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.name') border-rose-400 bg-rose-50 @enderror">
                        <p class="mt-1 text-xs text-slate-500">Solo para reconocerlo en el listado.</p>
                        @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Descuento</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo <span class="text-rose-500">*</span></label>
                            <select wire:model.live="form.discount_type" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm">
                                <option value="percentage">Porcentaje (%)</option>
                                <option value="amount">Valor fijo ($)</option>
                            </select>
                            @error('form.discount_type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">
                                {{ $form->discount_type === 'percentage' ? 'Porcentaje' : 'Valor' }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" wire:model="form.discount_value" step="0.01" min="0"
                                max="{{ $form->discount_type === 'percentage' ? 100 : '' }}"
                                placeholder="{{ $form->discount_type === 'percentage' ? '10' : '50000' }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm @error('form.discount_value') border-rose-400 bg-rose-50 @enderror">
                            @error('form.discount_value') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Subtotal mínimo de la OT</label>
                        <input type="number" wire:model="form.min_order_amount" step="0.01" min="0" placeholder="Sin mínimo"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm @error('form.min_order_amount') border-rose-400 bg-rose-50 @enderror">
                        <p class="mt-1 text-xs text-slate-500">Déjalo vacío si el cupón aplica a cualquier monto.</p>
                        @error('form.min_order_amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Vigencia y límite</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Desde</label>
                            <input type="date" wire:model="form.starts_at"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm @error('form.starts_at') border-rose-400 bg-rose-50 @enderror">
                            @error('form.starts_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Hasta</label>
                            <input type="date" wire:model="form.ends_at"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm @error('form.ends_at') border-rose-400 bg-rose-50 @enderror">
                            @error('form.ends_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Máximo de usos</label>
                            <input type="number" wire:model="form.max_uses" min="1" step="1" placeholder="Sin límite"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm @error('form.max_uses') border-rose-400 bg-rose-50 @enderror">
                            @error('form.max_uses') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Sin fechas, el cupón está vigente siempre.</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="form.active" class="custom-checkbox">
                    Cupón activo
                </label>
            </div>

            <div class="flex shrink-0 items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-6">
                <button type="button" wire:click="closeModal" class="btn btn-secondary btn-sm">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary btn-sm">
                    <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Guardar cambios' : 'Crear cupón' }}</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
