<div class="p-6 space-y-6" x-data="{ previewUrl: '{{ $business->logo ? Storage::disk('public')->url($business->logo) : '' }}', removeLogo: false }">

    {{-- Encabezado --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Mi Negocio</h1>
        <p class="text-sm text-slate-500 mt-1">Actualiza la información de tu comercio.</p>
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- Sección: Identidad --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-900">Identidad del negocio</h2>
            </div>
            <div class="p-6 space-y-5">

                {{-- Logo --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-3">Logo del comercio</label>
                    <div class="flex items-start gap-5">
                        {{-- Preview --}}
                        <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden bg-slate-50 flex items-center justify-center shrink-0">
                            <template x-if="previewUrl && !removeLogo">
                                <img :src="previewUrl" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!previewUrl || removeLogo">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </template>
                        </div>
                        {{-- Controles --}}
                        <div class="flex-1 space-y-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium px-4 py-2 rounded-xl transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Cambiar logo
                                <input type="file" wire:model="new_logo" accept="image/*" class="sr-only"
                                    x-on:change="
                                        removeLogo = false;
                                        $wire.set('remove_logo', false);
                                        const f = $event.target.files[0];
                                        if (f) { const r = new FileReader(); r.onload = e => previewUrl = e.target.result; r.readAsDataURL(f); }
                                    ">
                            </label>
                            <div wire:loading wire:target="new_logo" class="text-xs text-indigo-600">Subiendo imagen...</div>
                            @if($current_logo)
                            <button type="button"
                                x-on:click="removeLogo = true; previewUrl = ''; $wire.set('remove_logo', true)"
                                class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-700 font-medium px-3 py-1.5 rounded-lg hover:bg-rose-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Eliminar logo
                            </button>
                            @endif
                            @error('new_logo') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-slate-400">JPG, PNG o WebP · máximo 2 MB</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del comercio <span class="text-rose-500">*</span></label>
                        <input wire:model="name" type="text" placeholder="Mi Comercio S.A.S."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('name') border-rose-400 bg-rose-50 @enderror">
                        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- NIT (readonly) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            NIT / RUT
                            <span class="ml-1 inline-flex items-center gap-1 text-xs text-slate-400 font-normal">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                No editable
                            </span>
                        </label>
                        <input type="text" value="{{ $nit }}" readonly
                            class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm text-slate-500 cursor-not-allowed select-none">
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección: Contacto --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-900">Información de contacto</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Teléfono --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <input wire:model="phone_number" type="tel" placeholder="300 123 4567"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('phone_number') border-rose-400 bg-rose-50 @enderror">
                    </div>
                    @error('phone_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <input wire:model="email" type="email" placeholder="contacto@micomercio.com"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('email') border-rose-400 bg-rose-50 @enderror">
                    </div>
                    @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                {{-- Sitio web --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Sitio web</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <input wire:model="website" type="url" placeholder="https://micomercio.com"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('website') border-rose-400 bg-rose-50 @enderror">
                    </div>
                    @error('website') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                {{-- Ciudad --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Ciudad</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <select wire:model="city_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('city_id') border-rose-400 bg-rose-50 @enderror">
                            <option value="">Selecciona la ciudad</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('city_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                {{-- Dirección --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Dirección</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute top-3 left-0 flex items-start pl-3.5">
                            <svg class="w-4 h-4 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </div>
                        <textarea wire:model="address" rows="2" placeholder="Calle 123 # 45-67, Barrio, Ciudad"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition resize-none @error('address') border-rose-400 bg-rose-50 @enderror"></textarea>
                    </div>
                    @error('address') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Botón guardar --}}
        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl transition shadow-sm shadow-indigo-500/20 disabled:opacity-60"
                wire:loading.attr="disabled">
                <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5 5.373 5 12h4z"/></svg>
                <span wire:loading.remove>Guardar cambios</span>
                <span wire:loading>Guardando...</span>
            </button>
        </div>
    </form>
</div>
