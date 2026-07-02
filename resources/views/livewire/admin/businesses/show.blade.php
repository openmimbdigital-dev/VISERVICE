<div class="p-6 space-y-6" x-data="{ tab: 'info', logoPreview: '{{ $current_logo ? Storage::disk('public')->url($current_logo) : '' }}', removeLogo: false }">

    {{-- Encabezado --}}
    <div class="flex items-start gap-5">
        {{-- Logo / avatar --}}
        <div class="w-16 h-16 rounded-2xl overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center border border-slate-200 shadow-sm">
            @if($business->logo)
                <img src="{{ Storage::disk('public')->url($business->logo) }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
            @else
                <span class="text-xl font-bold text-slate-400">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-slate-900 truncate">{{ $business->name }}</h1>
                @if($business->status)
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo
                    </span>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-slate-500">
                <span>NIT: <span class="font-medium text-slate-700">{{ $business->nit }}</span></span>
                @if($business->business_type)
                    <span>Tipo: <span class="font-medium text-slate-700">{{ $business->business_type->name }}</span></span>
                @endif
                @if($business->city)
                    <span>{{ $business->city->name }}</span>
                @endif
                <span>Registrado {{ $business->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.businesses.index') }}" wire:navigate
                class="inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-50 px-3.5 py-2 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-slate-200">
        <nav class="flex gap-1 -mb-px">
            @foreach([['info','Información'],['subscriptions','Suscripciones'],['users','Usuarios']] as [$key,$label])
            <button type="button" @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}'
                    ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold'
                    : 'text-slate-500 hover:text-slate-700 border-b-2 border-transparent'"
                class="px-4 py-3 text-sm transition whitespace-nowrap">
                {{ $label }}
                @if($key === 'users')
                    <span class="ml-1.5 inline-flex items-center justify-center h-4.5 min-w-4.5 px-1.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold">
                        {{ $business->users->count() }}
                    </span>
                @elseif($key === 'subscriptions')
                    <span class="ml-1.5 inline-flex items-center justify-center h-4.5 min-w-4.5 px-1.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold">
                        {{ $business->subscriptions->count() }}
                    </span>
                @endif
            </button>
            @endforeach
        </nav>
    </div>

    {{-- ════════════════════ TAB: INFORMACIÓN ════════════════════ --}}
    <div x-show="tab === 'info'" x-cloak>
        @can('businesses.edit')
        <form wire:submit="save" class="space-y-5">

            {{-- Logo --}}
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900">Identidad visual</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-start gap-5">
                        <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden bg-slate-50 flex items-center justify-center shrink-0">
                            <template x-if="logoPreview && !removeLogo">
                                <img :src="logoPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoPreview || removeLogo">
                                <span class="text-2xl font-bold text-slate-300">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                            </template>
                        </div>
                        <div class="space-y-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium px-4 py-2 rounded-xl transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Cambiar logo
                                <input type="file" wire:model="new_logo" accept="image/*" class="sr-only"
                                    x-on:change="
                                        removeLogo = false; $wire.set('remove_logo', false);
                                        const f = $event.target.files[0];
                                        if (f) { const r = new FileReader(); r.onload = e => logoPreview = e.target.result; r.readAsDataURL(f); }
                                    ">
                            </label>
                            <div wire:loading wire:target="new_logo" class="text-xs text-indigo-600">Cargando...</div>
                            @if($current_logo)
                            <button type="button"
                                x-on:click="removeLogo = true; logoPreview = ''; $wire.set('remove_logo', true)"
                                class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-700 font-medium px-3 py-1.5 rounded-lg hover:bg-rose-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Eliminar logo
                            </button>
                            @endif
                            @error('new_logo') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="text-xs text-slate-400">JPG, PNG o WebP · máx. 2 MB</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datos generales --}}
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-900">Datos generales</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Nombre <span class="text-rose-500">*</span></label>
                        <input wire:model="name" type="text"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('name') border-rose-400 bg-rose-50 @enderror">
                        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">NIT / RUT <span class="text-rose-500">*</span></label>
                        <input wire:model="nit" type="text"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('nit') border-rose-400 bg-rose-50 @enderror">
                        @error('nit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Tipo de negocio</label>
                        <select wire:model="business_type_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                            <option value="">Sin tipo</option>
                            @foreach($business_types as $bt)
                                <option value="{{ $bt->id }}">{{ $bt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Ciudad</label>
                        <select wire:model="city_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                            <option value="">Sin ciudad</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input wire:model="phone_number" type="tel"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Correo electrónico</label>
                        <input wire:model="email" type="email"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('email') border-rose-400 bg-rose-50 @enderror">
                        @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Dirección</label>
                        <textarea wire:model="address" rows="2"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition resize-none"></textarea>
                    </div>

                    {{-- Estado --}}
                    @canany(['businesses.activate', 'businesses.deactivate'])
                    <div class="md:col-span-2 flex items-center justify-between bg-slate-50 rounded-xl px-4 py-3 border border-slate-200">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Estado del comercio</p>
                            <p class="text-xs text-slate-400 mt-0.5">Desactivar impide el acceso de todos sus usuarios.</p>
                        </div>
                        <button type="button" wire:click="$toggle('status')"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 {{ $status ? 'bg-indigo-600' : 'bg-slate-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $status ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                    @endcanany
                </div>
            </div>

            {{-- Redes sociales y web --}}
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-900">Presencia digital</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Sitio web</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            </div>
                            <input wire:model="website" type="url" placeholder="https://micomercio.com"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('website') border-rose-400 @enderror">
                        </div>
                        @error('website') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    @foreach([
                        ['field'=>'facebook','icon'=>'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z','label'=>'Facebook','color'=>'text-blue-600'],
                        ['field'=>'instagram','icon'=>'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M6.5 19.5h11a3 3 0 003-3v-11a3 3 0 00-3-3h-11a3 3 0 00-3 3v11a3 3 0 003 3z','label'=>'Instagram','color'=>'text-pink-500'],
                        ['field'=>'twitter','icon'=>'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z','label'=>'Twitter / X','color'=>'text-sky-500'],
                    ] as $sn)
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">{{ $sn['label'] }}</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="w-4 h-4 {{ $sn['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sn['icon'] }}"/></svg>
                            </div>
                            <input wire:model="{{ $sn['field'] }}" type="url" placeholder="https://..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Representante legal --}}
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-900">Representante legal</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Nombre completo</label>
                        <input wire:model="rep_name" type="text"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input wire:model="rep_phone" type="tel"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Correo</label>
                        <input wire:model="rep_email" type="email"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('rep_email') border-rose-400 @enderror">
                        @error('rep_email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Guardar --}}
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
        @else
        <div class="space-y-5">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Solo tienes permiso de lectura. No puedes editar los datos del negocio.
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-900">Datos generales</h2>
                </div>
                <dl class="divide-y divide-slate-100 px-6 py-2">
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->name }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">NIT / RUT</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->nit }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Tipo de negocio</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->business_type?->name ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Ciudad</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->city?->name ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Teléfono</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->phone_number ?: '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Correo</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->email ?: '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Dirección</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->address ?: '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Estado</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->status ? 'Activo' : 'Inactivo' }}</dd>
                    </div>
                </dl>
            </div>

            @php $rep = is_array($business->representative) ? $business->representative : []; @endphp
            @if(! empty(array_filter($rep)))
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-900">Representante legal</h2>
                </div>
                <dl class="divide-y divide-slate-100 px-6 py-2">
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $rep['name'] ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Teléfono</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $rep['phone'] ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Correo</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $rep['email'] ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
            @endif
        </div>
        @endcan
    </div>

    {{-- ════════════════════ TAB: SUSCRIPCIONES ════════════════════ --}}
    <div x-show="tab === 'subscriptions'" x-cloak class="space-y-4">
        @forelse($business->subscriptions->sortByDesc('created_at') as $sub)
        @php
            $subColor = match($sub->status) {
                'active'    => ['ring'=>'ring-emerald-500/30','bg'=>'bg-emerald-50','badge'=>'bg-emerald-100 text-emerald-700'],
                'pending'   => ['ring'=>'ring-amber-400/30', 'bg'=>'bg-amber-50', 'badge'=>'bg-amber-100 text-amber-700'],
                'trial'     => ['ring'=>'ring-blue-400/30',  'bg'=>'bg-blue-50',  'badge'=>'bg-blue-100 text-blue-700'],
                'past_due'  => ['ring'=>'ring-yellow-400/30','bg'=>'bg-yellow-50','badge'=>'bg-yellow-100 text-yellow-700'],
                'cancelled' => ['ring'=>'ring-slate-300',    'bg'=>'bg-slate-50', 'badge'=>'bg-slate-100 text-slate-500'],
                'expired'   => ['ring'=>'ring-rose-400/30',  'bg'=>'bg-rose-50',  'badge'=>'bg-rose-100 text-rose-700'],
                default     => ['ring'=>'ring-slate-300',    'bg'=>'bg-slate-50', 'badge'=>'bg-slate-100 text-slate-500'],
            };
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200 ring-2 {{ $subColor['ring'] }} overflow-hidden">
            {{-- Header suscripción --}}
            <div class="px-6 py-4 {{ $subColor['bg'] }} border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $sub->plan?->name ?? 'Plan eliminado' }}</p>
                        <p class="text-xs text-slate-500">{{ $sub->billing_cycle_label }} · Creada {{ $sub->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold text-slate-900">${{ number_format($sub->total_price, 0, ',', '.') }}</span>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $subColor['badge'] }}">{{ $sub->status_label }}</span>
                </div>
            </div>

            {{-- Detalles suscripción --}}
            <div class="px-6 py-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-xs text-slate-400">Inicio</p>
                    <p class="font-medium text-slate-800">{{ $sub->started_at?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Vence</p>
                    <p class="font-medium text-slate-800">{{ $sub->ends_at?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Precio mensual</p>
                    <p class="font-medium text-slate-800">${{ number_format($sub->monthly_price, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Descuento</p>
                    <p class="font-medium text-slate-800">{{ $sub->discount_percentage ?? 0 }}%</p>
                </div>
            </div>

            {{-- Facturas --}}
            @if($sub->invoices->isNotEmpty())
            <div class="border-t border-slate-100">
                <div class="px-6 py-3 bg-slate-50 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Facturas</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($sub->invoices->sortByDesc('created_at') as $inv)
                    @php
                        $invBadge = match($inv->status) {
                            'paid'     => 'bg-emerald-100 text-emerald-700',
                            'pending'  => 'bg-amber-100 text-amber-700',
                            'failed'   => 'bg-rose-100 text-rose-700',
                            'refunded' => 'bg-slate-100 text-slate-500',
                            default    => 'bg-slate-100 text-slate-500',
                        };
                        $invLabel = match($inv->status) {
                            'paid'     => 'Pagado',
                            'pending'  => 'Pendiente',
                            'failed'   => 'Fallido',
                            'refunded' => 'Reembolsado',
                            default    => $inv->status,
                        };
                    @endphp
                    <div class="px-6 py-3 flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-mono text-slate-500">{{ $inv->invoice_number }}</span>
                            <span class="text-xs text-slate-400">{{ $inv->created_at->format('d/m/Y') }}</span>
                            @if($inv->payment_method)
                                <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">
                                    {{ $inv->payment_method === 'transfer' ? 'Transferencia' : 'Efectivo' }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-slate-800">${{ number_format($inv->amount, 0, ',', '.') }}</span>
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $invBadge }}">{{ $invLabel }}</span>
                            @if($inv->payment_proof)
                                <a href="{{ Storage::disk('public')->url($inv->payment_proof) }}" target="_blank"
                                    class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Comprobante
                                </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-slate-200 py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <p class="mt-3 text-sm font-medium text-slate-900">Sin suscripciones</p>
            <p class="mt-1 text-xs text-slate-500">Este comercio no tiene historial de suscripciones.</p>
        </div>
        @endforelse
    </div>

    {{-- ════════════════════ TAB: USUARIOS ════════════════════ --}}
    <div x-show="tab === 'users'" x-cloak>
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            @if($business->users->isEmpty())
            <div class="py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="mt-3 text-sm font-medium text-slate-900">Sin usuarios registrados</p>
            </div>
            @else
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Usuario</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Rol</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Registro</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($business->users->sortBy('id') as $u)
                    @php
                        $isPrimary = $u->id === $primaryUserId;
                        $roleName  = $u->getRoleNames()->first() ?? '—';
                        $roleBg    = match($roleName) {
                            'superAdmin'    => 'bg-rose-100 text-rose-700',
                            'Comercio'      => 'bg-violet-100 text-violet-700',
                            'Administrador' => 'bg-indigo-100 text-indigo-700',
                            'Supervisor'    => 'bg-sky-100 text-sky-700',
                            default         => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-violet-500 to-indigo-700 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-white">
                                        {{ strtoupper(substr($u->first_name ?? '', 0, 1) . substr($u->last_name ?? '', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $u->full_name }}
                                        @if($isPrimary)
                                            <span class="ml-1 text-[10px] font-medium text-violet-600 bg-violet-50 px-1.5 py-0.5 rounded-full">Principal</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400">{{ $u->email }}</p>
                                    @if($u->username)
                                        <p class="text-xs text-slate-400">{{ $u->username }}</p>
                                    @endif
                                    @if($u->phone_number)
                                        <p class="text-xs text-slate-400">{{ $u->phone_number }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $roleBg }}">{{ $roleName }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @if($isPrimary)
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full {{ $u->status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $u->status ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $u->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            @else
                                @canany(['users.activate', 'users.deactivate'])
                                <button wire:click="toggleUserStatus({{ $u->id }})" type="button"
                                    title="{{ $u->status ? 'Clic para desactivar' : 'Clic para activar' }}">
                                    @if($u->status)
                                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full hover:bg-emerald-200 transition cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full hover:bg-slate-200 transition cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo
                                        </span>
                                    @endif
                                </button>
                                @else
                                    @if($u->status)
                                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo
                                        </span>
                                    @endif
                                @endcanany
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm text-slate-700">{{ $u->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-400">{{ $u->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @can('users.view')
                            <a href="{{ route('admin.users.index') }}" wire:navigate
                                class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Gestionar
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
