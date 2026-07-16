<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6" x-data="{ previewUrl: @js($current_logo_url ?? ''), removeLogo: false }">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.businesses.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Negocios</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $is_editing ? 'Editar negocio' : 'Nuevo negocio' }}</span>
    </nav>

    <header class="mb-8">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ $is_editing ? 'Editar negocio' : 'Nuevo negocio' }}
            </h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                {{ $is_editing
                    ? 'Actualiza los datos del negocio. El tipo de negocio se define según el tipo de organización.'
                    : 'Registra un nuevo negocio. Selecciona el tipo de organización; el tipo de negocio se asignará automáticamente.' }}
            </p>
        </div>
    </header>

    <form wire:submit="save" class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Datos del negocio</h2>
        </div>

        <div class="space-y-6 p-4 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo de negocio <span class="text-rose-500">*</span></label>
                    <select wire:model="form.business_type_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.business_type_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">— Seleccionar —</option>
                        @foreach($business_types as $business_type)
                            <option value="{{ $business_type->id }}">{{ $business_type->name }}</option>
                        @endforeach
                    </select>
                    @error('form.business_type_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <p class="mt-1.5 text-xs text-slate-500">El tipo de organización se determina automáticamente según esta selección.</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.name"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.name') border-rose-400 bg-rose-50 @enderror">
                    @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Eslogan / línea comercial</label>
                    <input type="text" wire:model="form.tagline" placeholder="Ej. Su taller de confianza"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.tagline') border-rose-400 bg-rose-50 @enderror">
                    @error('form.tagline') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-500">Aparece en cotizaciones y documentos del negocio.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Régimen tributario</label>
                    <input type="text" wire:model="form.tax_regime" placeholder="Ej. Responsable de IVA"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.tax_regime') border-rose-400 bg-rose-50 @enderror">
                    @error('form.tax_regime') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">NIT <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.nit"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.nit') border-rose-400 bg-rose-50 @enderror">
                    @error('form.nit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Teléfono</label>
                    <input type="text" wire:model="form.phone_number"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.phone_number') border-rose-400 bg-rose-50 @enderror">
                    @error('form.phone_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Correo electrónico</label>
                    <input type="email" wire:model="form.email"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.email') border-rose-400 bg-rose-50 @enderror">
                    @error('form.email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Ciudad</label>
                    <select wire:model="form.city_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.city_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">— Seleccionar —</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                    @error('form.city_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Dirección</label>
                    <input type="text" wire:model="form.address"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.address') border-rose-400 bg-rose-50 @enderror">
                    @error('form.address') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Sitio web</label>
                    <input type="url" wire:model="form.website" placeholder="https://"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.website') border-rose-400 bg-rose-50 @enderror">
                    @error('form.website') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-800">Redes sociales</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Facebook</label>
                        <input type="text" wire:model="form.facebook" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Instagram</label>
                        <input type="text" wire:model="form.instagram" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Twitter / X</label>
                        <input type="text" wire:model="form.twitter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-800">Representante legal</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre</label>
                        <input type="text" wire:model="form.rep_name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Teléfono</label>
                        <input type="text" wire:model="form.rep_phone" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Correo</label>
                        <input type="email" wire:model="form.rep_email" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.rep_email') border-rose-400 bg-rose-50 @enderror">
                        @error('form.rep_email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-800">Logo</h3>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-200 bg-slate-50">
                        <template x-if="previewUrl && !removeLogo">
                            <img :src="previewUrl" alt="Vista previa del logo" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!previewUrl || removeLogo">
                            <span class="text-lg font-bold text-slate-300">{{ strtoupper(mb_substr($form->name ?: 'NE', 0, 2)) }}</span>
                        </template>
                    </div>
                    <div class="min-w-0 flex-1 space-y-2">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Seleccionar imagen
                            <input type="file" wire:model="new_logo" accept="image/*" class="sr-only"
                                x-on:change="
                                    removeLogo = false;
                                    $wire.set('remove_logo', false);
                                    const file = $event.target.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = e => previewUrl = e.target.result;
                                        reader.readAsDataURL(file);
                                    }
                                ">
                        </label>
                        <div wire:loading wire:target="new_logo" class="text-xs text-indigo-600">Subiendo imagen...</div>
                        @if($current_logo_url)
                            <button type="button"
                                x-on:click="removeLogo = true; previewUrl = ''; $wire.set('remove_logo', true)"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 transition hover:bg-rose-50 hover:text-rose-700">
                                Eliminar logo
                            </button>
                        @endif
                        @error('new_logo') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        <p class="text-xs text-slate-400">JPG, PNG o WebP · máximo 2 MB</p>
                    </div>
                </div>
            </div>

            @if($can_edit_status)
            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$toggle('form.status')"
                        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200 {{ $form->status ? 'bg-indigo-600' : 'bg-slate-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $form->status ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                    <span class="text-sm {{ $form->status ? 'font-medium text-emerald-700' : 'text-slate-500' }}">
                        {{ $form->status ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
            @endif
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <a href="{{ $is_editing ? route('admin.businesses.show', $form->business_id) : route('admin.businesses.index') }}" wire:navigate
                class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-center text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                {{ $is_editing ? 'Guardar cambios' : 'Crear negocio' }}
            </button>
        </div>
    </form>
</div>
