<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.businesses.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Negocios</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Ítems públicos — {{ $section_label }}</span>
    </nav>

    <header class="mb-8">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Gestión de negocios</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Ítems públicos — {{ $section_label }}</h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                Elige el tipo de organización y marca qué ítems de la sección pública <strong>{{ $section_label }}</strong> podrán verse en el portal.
            </p>
        </div>
    </header>

    <form wire:submit="save" class="space-y-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-800">Tipo de organización</h2>
            </div>
            <div class="p-4 sm:p-5">
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo <span class="text-rose-500">*</span></label>
                <select wire:model.live="organization_type_id"
                    class="w-full max-w-md rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">— Seleccionar —</option>
                    @foreach($organization_types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('organization_type_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </section>

        @if($organization_type_id)
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <h2 class="font-semibold text-slate-800">Ítems de la sección {{ $section_label }}</h2>
                    <p class="mt-1 text-xs text-slate-500">Solo los marcados aparecerán en el menú del portal público.</p>
                </div>
                <div class="p-4 sm:p-5">
                    @if($items === [])
                        <p class="text-sm text-slate-500">No hay ítems configurados en el catálogo.</p>
                    @else
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($items as $key => $item)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-100 px-3 py-3 transition hover:bg-slate-50">
                                    <input type="checkbox" wire:model="selected_item_keys" value="{{ $key }}"
                                        class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                                    <span class="text-sm font-medium text-slate-800">{{ $item['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:px-5">
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-primary w-full justify-center disabled:opacity-60 sm:w-auto">
                        <span wire:loading.remove wire:target="save">Guardar</span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </button>
                </div>
            </section>
        @endif
    </form>
</div>
