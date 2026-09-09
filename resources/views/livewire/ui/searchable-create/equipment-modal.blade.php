<x-ui.modal maxWidth="2xl">
    <x-slot:backdrop>
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="close"></div>
    </x-slot:backdrop>

    <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
        <div class="min-w-0">
            <h3 class="text-base font-semibold text-slate-900">Nuevo equipo</h3>
            @if($client_name)
            <p class="mt-0.5 truncate text-xs text-slate-500">Para {{ $client_name }}</p>
            @endif
        </div>
        <button type="button" wire:click="close" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
        <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo de equipo <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.equipment_type_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.equipment_type_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar tipo</option>
                        @foreach($equipment_types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('form.equipment_type_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Marca <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.brand_id" @disabled(! $form->equipment_type_id)
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm disabled:opacity-60 @error('form.brand_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">{{ $form->equipment_type_id ? 'Seleccionar marca' : 'Primero elige el tipo' }}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    @error('form.brand_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    @if($form->equipment_type_id && $brands->isEmpty())
                    <p class="mt-1 text-xs text-amber-700">Este tipo de equipo no tiene marcas asociadas.</p>
                    @endif
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Modelo <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.model_id" @disabled(! $form->brand_id)
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm disabled:opacity-60 @error('form.model_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">{{ $form->brand_id ? 'Seleccionar modelo' : 'Primero elige la marca' }}</option>
                        @foreach($models as $model)
                            <option value="{{ $model->id }}">{{ $model->name }}</option>
                        @endforeach
                    </select>
                    @error('form.model_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    @if($form->brand_id && $models->isEmpty())
                    <p class="mt-1 text-xs text-amber-700">Esta marca no tiene modelos registrados.</p>
                    @endif
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.name" placeholder="Como lo identifica el cliente"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.name') border-rose-400 bg-rose-50 @enderror">
                    @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Placa <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.plate" placeholder="ABC123"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm uppercase @error('form.plate') border-rose-400 bg-rose-50 @enderror">
                    @error('form.plate') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Año <span class="text-rose-500">*</span></label>
                    <input type="number" wire:model="form.year" min="1900" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.year') border-rose-400 bg-rose-50 @enderror">
                    @error('form.year') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Notas</label>
                    <input type="text" wire:model="form.notes" placeholder="Opcional"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
            </div>

            <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-500">
                El equipo queda disponible de inmediato para esta orden. Los detalles adicionales
                del tipo de equipo se pueden completar después desde el módulo de equipos.
            </p>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <button type="button" wire:click="close" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                <span wire:loading.remove wire:target="save">Guardar equipo</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    </form>
</x-ui.modal>
