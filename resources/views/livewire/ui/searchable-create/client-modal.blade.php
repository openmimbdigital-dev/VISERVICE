<x-ui.modal maxWidth="2xl">
    <x-slot:backdrop>
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="close"></div>
    </x-slot:backdrop>

    <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
        <h3 class="text-base font-semibold text-slate-900">Nuevo cliente</h3>
        <button type="button" wire:click="close" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
        <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @if($is_super_admin)
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Comercio <span class="text-rose-500">*</span></label>
                    <select wire:model="form.business_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.business_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar comercio</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                    @error('form.business_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                @endif

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre / Razón social <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.name') border-rose-400 bg-rose-50 @enderror">
                    @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Ciudad <span class="text-rose-500">*</span></label>
                    <select wire:model="form.city_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.city_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar ciudad</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}{{ $city->state_province ? ' — ' . $city->state_province : '' }}</option>
                        @endforeach
                    </select>
                    @error('form.city_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Contacto</label>
                    <input type="text" wire:model="form.contact_name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo documento <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.document_type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.document_type') border-rose-400 bg-rose-50 @enderror">
                        @foreach(['CC', 'NIT', 'CE', 'PA', 'PPT', 'TI'] as $tipo)
                            <option value="{{ $tipo }}">{{ $tipo }}</option>
                        @endforeach
                    </select>
                    @error('form.document_type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Número documento</label>
                    <input type="text" wire:model.blur="form.document_number" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.document_number') border-rose-400 bg-rose-50 @enderror">
                    @error('form.document_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                @if($form->document_type === 'NIT')
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Dígito de verificación</label>
                    <input type="text" maxlength="1" wire:model="form.verification_digit" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-mono">
                    @error('form.verification_digit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                @endif

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo de persona <span class="text-rose-500">*</span></label>
                    <select wire:model="form.person_type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.person_type') border-rose-400 bg-rose-50 @enderror">
                        @foreach(config('dian.person_types') as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('form.person_type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Responsabilidad fiscal <span class="text-rose-500">*</span></label>
                    <select wire:model="form.fiscal_responsibilities" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.fiscal_responsibilities') border-rose-400 bg-rose-50 @enderror">
                        @foreach(config('dian.fiscal_responsibilities') as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('form.fiscal_responsibilities') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Teléfono</label>
                    <input type="text" wire:model="form.phone" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Email</label>
                    <input type="email" wire:model="form.email" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.email') border-rose-400 bg-rose-50 @enderror">
                    @error('form.email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Dirección</label>
                    <input type="text" wire:model="form.address" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
            </div>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <button type="button" wire:click="close" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
            <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                Guardar cliente
            </button>
        </div>
    </form>
</x-ui.modal>
