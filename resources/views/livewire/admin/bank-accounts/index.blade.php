<div class="p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Cuentas Bancarias</h1>
            <p class="text-sm text-slate-500 mt-1">Gestiona las cuentas donde los comercios realizan sus pagos.</p>
        </div>
        <button wire:click="openCreate" type="button"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nueva cuenta
        </button>
    </div>

    {{-- Lista de cuentas --}}
    @if($accounts->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 py-16 text-center">
        <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
        </div>
        <p class="text-sm font-medium text-slate-900">No hay cuentas bancarias</p>
        <p class="mt-1 text-xs text-slate-500">Agrega la primera cuenta para mostrarla en el onboarding.</p>
        <button wire:click="openCreate" type="button"
            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Agregar cuenta
        </button>
    </div>
    @else
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($accounts as $account)
        @php
            $bankLogo = $account->logo
                ? Storage::disk('public')->url($account->logo)
                : ($account->bank?->logo ? Storage::disk('public')->url($account->bank->logo) : null);
        @endphp
        <div @class([
            'bg-white rounded-2xl border overflow-hidden transition-all',
            'border-slate-200' => $account->is_active,
            'border-slate-200 opacity-60' => !$account->is_active,
        ])>
            {{-- Header de la tarjeta --}}
            <div @class([
                'px-5 py-4 flex items-center justify-between border-b',
                'border-slate-100 bg-gradient-to-r from-indigo-50 to-white' => $account->is_active,
                'border-slate-100 bg-slate-50' => !$account->is_active,
            ])>
                <div class="flex items-center gap-3">
                    {{-- Logo del banco --}}
                    <div @class([
                        'w-11 h-11 rounded-xl flex items-center justify-center shrink-0 overflow-hidden border',
                        'border-indigo-100 bg-indigo-50' => $account->is_active && !$bankLogo,
                        'border-slate-200 bg-slate-100' => !$account->is_active && !$bankLogo,
                        'border-slate-200 bg-white' => $bankLogo,
                    ])>
                        @if($bankLogo)
                            <img src="{{ $bankLogo }}"
                                 alt="{{ $account->bank_name }}"
                                 class="w-full h-full object-contain p-1">
                        @else
                            <svg class="w-5 h-5 {{ $account->is_active ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900 text-sm truncate">{{ $account->bank_name }}</p>
                        <span @class([
                            'inline-block text-xs font-medium px-2 py-0.5 rounded-full mt-0.5',
                            'bg-indigo-100 text-indigo-700' => $account->account_type === 'corriente',
                            'bg-emerald-100 text-emerald-700' => $account->account_type === 'ahorros',
                        ])>{{ $account->account_type_label }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    @if(!$account->is_active)
                        <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">Inactiva</span>
                    @endif
                </div>
            </div>

            {{-- Datos de la cuenta --}}
            <div class="px-5 py-4 space-y-2.5">
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <div>
                        <p class="text-xs text-slate-400 leading-none">Número de cuenta</p>
                        <p class="text-sm font-mono font-semibold text-slate-800 mt-0.5">{{ $account->account_number }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <div>
                        <p class="text-xs text-slate-400 leading-none">Titular</p>
                        <p class="text-sm text-slate-800 mt-0.5">{{ $account->account_holder }}</p>
                        <p class="text-xs text-slate-500">{{ $account->document_type }}: {{ $account->document_number }}</p>
                    </div>
                </div>
                @if($account->notes)
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs text-slate-500">{{ $account->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Acciones --}}
            <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between gap-2">
                <button wire:click="toggleActive({{ $account->id }})" type="button"
                    class="inline-flex items-center gap-1.5 text-xs font-medium rounded-lg px-3 py-1.5 transition
                        {{ $account->is_active ? 'text-amber-700 bg-amber-50 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                    @if($account->is_active)
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Desactivar
                    @else
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Activar
                    @endif
                </button>
                <div class="flex items-center gap-1.5">
                    <button wire:click="openEdit({{ $account->id }})" type="button"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg px-3 py-1.5 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </button>
                    <button wire:click="delete({{ $account->id }})"
                        wire:confirm="¿Seguro que deseas eliminar esta cuenta bancaria?"
                        type="button"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg px-3 py-1.5 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Modal Crear / Editar --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        x-data="{
            preview: '{{ $current_logo ? Storage::disk('public')->url($current_logo) : '' }}',
            removeLogo: false
        }"
        x-on:keydown.escape.window="$wire.closeModal()">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">
                    {{ $selected_id ? 'Editar cuenta bancaria' : 'Nueva cuenta bancaria' }}
                </h3>
                <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4 max-h-[75vh] overflow-y-auto">

                {{-- Banco --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Banco <span class="text-red-500">*</span></label>
                    <select wire:model="bank_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('bank_id') border-red-400 bg-red-50 @enderror">
                        <option value="">— Selecciona un banco —</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                        @endforeach
                    </select>
                    @error('bank_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Logo de la cuenta (opcional) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Logo de la cuenta
                        <span class="text-slate-400 font-normal text-xs ml-1">(opcional — sobreescribe el logo del banco)</span>
                    </label>
                    <div class="flex items-center gap-4">
                        {{-- Preview --}}
                        <div class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center shrink-0 overflow-hidden">
                            <template x-if="preview && !removeLogo">
                                <img :src="preview" class="w-full h-full object-contain p-1">
                            </template>
                            <template x-if="!preview || removeLogo">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                            </template>
                        </div>
                        {{-- Controles --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium px-3 py-1.5 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Subir logo
                                <input type="file" wire:model="new_logo" accept="image/*" class="sr-only"
                                    x-on:change="
                                        removeLogo = false; $wire.set('remove_logo', false);
                                        const f = $event.target.files[0];
                                        if (f) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); }
                                    ">
                            </label>
                            <div wire:loading wire:target="new_logo" class="text-xs text-indigo-600">Cargando...</div>
                            @if($current_logo)
                            <button type="button"
                                x-on:click="removeLogo = true; preview = ''; $wire.set('remove_logo', true)"
                                class="inline-flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 font-medium px-2 py-1 rounded-lg hover:bg-rose-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Eliminar
                            </button>
                            @endif
                            @error('new_logo') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="text-[11px] text-slate-400">JPG, PNG o WebP · máx. 2 MB</p>
                        </div>
                    </div>
                </div>

                {{-- Tipo + Número --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de cuenta <span class="text-red-500">*</span></label>
                        <select wire:model="account_type"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('account_type') border-red-400 @enderror">
                            <option value="ahorros">Ahorros</option>
                            <option value="corriente">Corriente</option>
                        </select>
                        @error('account_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Número de cuenta <span class="text-red-500">*</span></label>
                        <input wire:model="account_number" type="text" placeholder="Ej: 123-456789-00"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('account_number') border-red-400 bg-red-50 @enderror">
                        @error('account_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Titular --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Titular de la cuenta <span class="text-red-500">*</span></label>
                    <input wire:model="account_holder" type="text" placeholder="Ej: SouulBi S.A.S."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('account_holder') border-red-400 bg-red-50 @enderror">
                    @error('account_holder') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Doc tipo + número --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de documento <span class="text-red-500">*</span></label>
                        <select wire:model="document_type"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                            <option value="NIT">NIT</option>
                            <option value="CC">Cédula (CC)</option>
                            <option value="CE">Cédula extranjería (CE)</option>
                        </select>
                        @error('document_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Número de documento <span class="text-red-500">*</span></label>
                        <input wire:model="document_number" type="text" placeholder="Ej: 900.123.456-7"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('document_number') border-red-400 bg-red-50 @enderror">
                        @error('document_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Notas --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Notas (opcional)</label>
                    <textarea wire:model="notes" rows="2" placeholder="Información adicional sobre esta cuenta..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition resize-none"></textarea>
                </div>

                {{-- Estado --}}
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$toggle('is_active')"
                        @class([
                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200',
                            'bg-indigo-600' => $is_active,
                            'bg-slate-200'  => !$is_active,
                        ])>
                        <span @class([
                            'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200',
                            'translate-x-6' => $is_active,
                            'translate-x-1' => !$is_active,
                        ])></span>
                    </button>
                    <span class="text-sm font-medium text-slate-700">
                        {{ $is_active ? 'Cuenta activa (visible en onboarding)' : 'Cuenta inactiva (oculta en onboarding)' }}
                    </span>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                <button wire:click="closeModal" type="button"
                    class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </button>
                <button wire:click="save" type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $selected_id ? 'Guardar cambios' : 'Crear cuenta' }}
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
