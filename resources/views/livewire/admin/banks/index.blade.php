<div class="p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Bancos</h1>
            <p class="text-sm text-slate-500 mt-1">Lista de bancos disponibles para las cuentas de pago.</p>
        </div>
        <button wire:click="openCreate" type="button"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo banco
        </button>
    </div>

    {{-- Filtro --}}
    <div class="max-w-sm">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar banco..."
                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        @if($banks->isEmpty())
        <div class="py-16 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
            </div>
            <p class="text-sm font-medium text-slate-900">No se encontraron bancos</p>
            <p class="mt-1 text-xs text-slate-500">Crea el primero o ajusta el filtro de búsqueda.</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-left">Banco</th>
                    <th class="px-5 py-3 text-left">Código</th>
                    <th class="px-5 py-3 text-center">Cuentas</th>
                    <th class="px-5 py-3 text-center">Estado</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($banks as $bank)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center shrink-0 overflow-hidden">
                                @if($bank->logo)
                                    <img src="{{ Storage::disk('public')->url($bank->logo) }}"
                                         alt="{{ $bank->name }}"
                                         class="w-full h-full object-contain p-0.5">
                                @else
                                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                @endif
                            </div>
                            <span class="font-medium text-slate-900">{{ $bank->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        @if($bank->code)
                            <span class="font-mono text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">{{ $bank->code }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($bank->bank_accounts_count > 0)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">
                                {{ $bank->bank_accounts_count }}
                            </span>
                        @else
                            <span class="text-slate-300 text-xs">0</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <button wire:click="toggleActive({{ $bank->id }})" type="button"
                            class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full transition
                                {{ $bank->is_active
                                    ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                                    : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $bank->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $bank->is_active ? 'Activo' : 'Inactivo' }}
                        </button>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-2">
                            <button wire:click="openEdit({{ $bank->id }})" type="button"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg px-3 py-1.5 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Editar
                            </button>
                            <button wire:click="delete({{ $bank->id }})"
                                wire:confirm="¿Seguro que deseas eliminar este banco?"
                                type="button"
                                @class([
                                    'inline-flex items-center gap-1.5 text-xs font-medium rounded-lg px-3 py-1.5 transition',
                                    'text-red-600 bg-red-50 hover:bg-red-100' => $bank->bank_accounts_count === 0,
                                    'text-slate-400 bg-slate-100 cursor-not-allowed' => $bank->bank_accounts_count > 0,
                                ])
                                @if($bank->bank_accounts_count > 0) title="Tiene cuentas asociadas" @endif>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($banks->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $banks->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Modal Crear / Editar --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        x-data="{
            preview: '{{ $current_logo ? Storage::disk('public')->url($current_logo) : '' }}',
            removeLogo: false
        }"
        x-on:keydown.escape.window="$wire.closeModal()">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">
                    {{ $selected_id ? 'Editar banco' : 'Nuevo banco' }}
                </h3>
                <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">

                {{-- Logo --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Logo del banco</label>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center shrink-0 overflow-hidden">
                            <template x-if="preview && !removeLogo">
                                <img :src="preview" class="w-full h-full object-contain p-1">
                            </template>
                            <template x-if="!preview || removeLogo">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </template>
                        </div>
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
                            <p class="text-[11px] text-slate-400">JPG, PNG, WebP o SVG · máx. 2 MB</p>
                        </div>
                    </div>
                </div>

                {{-- Nombre --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" placeholder="Ej: Bancolombia"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('name') border-red-400 bg-red-50 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Código --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Código bancario
                        <span class="text-slate-400 font-normal text-xs ml-1">(opcional)</span>
                    </label>
                    <input wire:model="code" type="text" placeholder="Ej: 007"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('code') border-red-400 bg-red-50 @enderror">
                    @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Estado --}}
                <div class="flex items-center gap-3 pt-1">
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
                        {{ $is_active ? 'Activo (disponible en cuentas)' : 'Inactivo (oculto en selector)' }}
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
                    {{ $selected_id ? 'Guardar cambios' : 'Crear banco' }}
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
