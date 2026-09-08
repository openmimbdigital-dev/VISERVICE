<x-ui.modal centered maxWidth="xl">
    <x-slot:backdrop>
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="close"></div>
    </x-slot:backdrop>

    <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
        <h3 class="text-base font-semibold text-slate-900">Nueva cuenta bancaria</h3>
        <button type="button" wire:click="close" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
        <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
            @if($is_super_admin)
            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Negocio <span class="text-rose-500">*</span></label>
                <select wire:model="form.business_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.business_id') border-rose-400 bg-rose-50 @enderror">
                    <option value="">Seleccionar negocio</option>
                    @foreach($businesses as $business)
                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                    @endforeach
                </select>
                @error('form.business_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Banco <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.bank_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.bank_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar banco</option>
                        @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                        @endforeach
                    </select>
                    @error('form.bank_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo de cuenta <span class="text-rose-500">*</span></label>
                    <select wire:model="form.account_type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.account_type') border-rose-400 bg-rose-50 @enderror">
                        @foreach($account_types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('form.account_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Número de cuenta <span class="text-rose-500">*</span></label>
                    <input wire:model="form.account_number" type="text"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.account_number') border-rose-400 bg-rose-50 @enderror">
                    @error('form.account_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Titular <span class="text-rose-500">*</span></label>
                    <input wire:model="form.account_holder" type="text"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.account_holder') border-rose-400 bg-rose-50 @enderror">
                    @error('form.account_holder')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">NIT <span class="text-rose-500">*</span></label>
                    <input wire:model="form.document_number" type="text"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.document_number') border-rose-400 bg-rose-50 @enderror">
                    @error('form.document_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <button type="button" wire:click="close" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
            <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                Crear cuenta
            </button>
        </div>
    </form>
</x-ui.modal>
