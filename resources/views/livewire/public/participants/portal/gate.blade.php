<div>
    @if($pin_not_configured)
        <header class="mb-8 border-l-4 border-amber-500 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-600/90">Portal de participantes</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Portal no disponible</h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                El portal de <strong>{{ $business_name }}</strong> aún no tiene un PIN de acceso configurado.
                Contacta al administrador del negocio.
            </p>
        </header>
    @else
    <header class="mb-8 border-l-4 border-indigo-600 pl-4 sm:pl-5">
        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Portal de participantes</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Acceso</h1>
        <p class="mt-2 max-w-xl text-sm text-slate-600">
            Ingresa el PIN de acceso y tu documento para entrar al portal de <strong>{{ $business_name }}</strong>.
        </p>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <form wire:submit="authenticate" class="p-5 sm:p-6" x-data="{ showPin: false }">
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">PIN de acceso <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="password"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            wire:model="pin"
                            placeholder="000000"
                            x-bind:type="showPin ? 'text' : 'password'"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 pr-11 text-sm tracking-[0.3em] transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('pin') border-rose-400 bg-rose-50 @enderror">
                        <button type="button"
                            @click="showPin = ! showPin"
                            x-bind:aria-label="showPin ? 'Ocultar PIN' : 'Mostrar PIN'"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-slate-600">
                            <svg x-show="! showPin" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPin" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('pin') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo de documento <span class="text-rose-500">*</span></label>
                        <select wire:model="document_type"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('document_type') border-rose-400 bg-rose-50 @enderror">
                            <option value="">Seleccione…</option>
                            @foreach($document_types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('document_type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Número de documento <span class="text-rose-500">*</span></label>
                        <input type="text"
                            wire:model="document_number"
                            autocomplete="off"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('document_number') border-rose-400 bg-rose-50 @enderror">
                        @error('document_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3">
                <button type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                    Ingresar al portal
                </button>
            </div>
        </form>
    </section>
    @endif
</div>
