<x-ui.modal centered maxWidth="md">
    <x-slot:backdrop>
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="close"></div>
    </x-slot:backdrop>

    <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
        <h3 class="text-base font-semibold text-slate-900">Nuevo impuesto</h3>
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

            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
                <input wire:model="form.name" type="text" placeholder="Ej. IVA"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.name') border-rose-400 bg-rose-50 @enderror">
                @error('form.name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Porcentaje <span class="text-rose-500">*</span></label>
                <input wire:model="form.percentage" type="number" min="0" max="100" step="0.01" placeholder="19"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.percentage') border-rose-400 bg-rose-50 @enderror">
                @error('form.percentage')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <button type="button" wire:click="close" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
            <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                Crear impuesto
            </button>
        </div>
    </form>
</x-ui.modal>
