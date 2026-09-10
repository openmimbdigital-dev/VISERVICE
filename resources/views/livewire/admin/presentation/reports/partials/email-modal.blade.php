@if($showEmailModal)
<x-ui.modal centered maxWidth="md">
    <x-slot:backdrop>
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeEmailModal"></div>
    </x-slot:backdrop>

    <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
        <h3 class="text-base font-semibold text-slate-900">Enviar reporte por correo</h3>
        <button type="button" wire:click="closeEmailModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <form wire:submit="sendEmail" class="flex min-h-0 flex-1 flex-col">
        <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Correo <span class="text-rose-500">*</span></label>
                <input type="email" wire:model="email" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('email') border-rose-400 bg-rose-50 @enderror">
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Mensaje</label>
                <textarea wire:model="email_note" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"></textarea>
            </div>
        </div>
        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <button type="button" wire:click="closeEmailModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
            <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">Enviar PDF</button>
        </div>
    </form>
</x-ui.modal>
@endif
