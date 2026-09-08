<div class="relative mx-auto w-full max-w-[90rem]">
    <nav class="mb-6 flex items-center gap-x-2 text-xs font-medium text-slate-400">
        <a href="{{ route('dashboard') }}" wire:navigate class="transition hover:text-indigo-600">Inicio</a>
        <span>/</span>
        <span class="text-slate-600">Guías y documentación</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Plataforma</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Guías y documentación</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Puestas en marcha y documentación interna de cada módulo. Solo las ve el superAdmin.
                </p>
            </div>
            <div class="grid shrink-0 grid-cols-2 gap-3 sm:max-w-xs">
                <div class="rounded-2xl border border-slate-200/90 bg-white px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Guías</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $total }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/90 bg-white px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Módulos</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $modules->count() }}</p>
                </div>
            </div>
        </div>
    </header>

    <section class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-col gap-2 sm:flex-row">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar en títulos y contenido..."
                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm sm:max-w-xs">
            <select wire:model.live="module_filter" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm sm:w-56">
                <option value="">Todos los módulos</option>
                @foreach($modules as $module)
                <option value="{{ $module }}">{{ $module }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="openCreate" class="btn btn-primary btn-sm shrink-0">Nueva guía</button>
    </section>

    @forelse($grouped_guides as $module => $guides)
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-3">
            <h2 class="font-semibold text-slate-800">{{ $module }}</h2>
            <span class="rounded-full bg-slate-200/70 px-2 py-0.5 text-[11px] font-semibold text-slate-600">{{ $guides->count() }}</span>
        </div>
        <ul class="divide-y divide-slate-100">
            @foreach($guides as $guide)
            <li wire:key="guide-{{ $guide->id }}" class="flex items-start gap-3 px-5 py-4 transition hover:bg-slate-50/60">
                <span class="mt-0.5 shrink-0 rounded-lg p-2 {{ $guide->type === 'onboarding' ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600' }}">
                    @if($guide->type === 'onboarding')
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <a href="{{ route('admin.guides.show', $guide) }}" wire:navigate class="group flex items-center gap-2">
                        <span class="truncate text-sm font-semibold text-slate-800 group-hover:text-indigo-700">{{ $guide->title }}</span>
                        @unless($guide->published)
                        <span class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-amber-600/20">Borrador</span>
                        @endunless
                    </a>
                    @if($guide->summary)
                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-500">{{ $guide->summary }}</p>
                    @endif
                    <p class="mt-1 text-[11px] text-slate-400">{{ $guide->typeLabel() }} · actualizada {{ $guide->updated_at?->diffForHumans() }}</p>
                </div>

                <div class="flex shrink-0 items-center gap-1">
                    <a href="{{ route('admin.guides.show', $guide) }}" wire:navigate title="Ver"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    <button type="button" wire:click="openEdit({{ $guide->id }})" title="Editar"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button type="button" wire:click="deleteGuide({{ $guide->id }})" title="Eliminar"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </li>
            @endforeach
        </ul>
    </section>
    @empty
    <section class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
        <p class="text-sm text-slate-500">
            {{ $search !== '' || $module_filter !== '' ? 'Ninguna guía coincide con la búsqueda.' : 'Todavía no hay guías.' }}
        </p>
        <button type="button" wire:click="openCreate" class="btn btn-primary btn-sm mt-4">Crear la primera</button>
    </section>
    @endforelse

    @if($showModal)
    <x-ui.modal centered maxWidth="2xl">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">{{ $form->isEditing() ? 'Editar guía' : 'Nueva guía' }}</h3>
            <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Título <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.title" placeholder="Ej. Habilitar un negocio para facturar ante la DIAN"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.title') border-rose-400 bg-rose-50 @enderror">
                    @error('form.title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Módulo <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="form.module" list="guide-modules" placeholder="Ej. Facturación electrónica"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.module') border-rose-400 bg-rose-50 @enderror">
                        <datalist id="guide-modules">
                            @foreach($modules as $module)
                            <option value="{{ $module }}"></option>
                            @endforeach
                        </datalist>
                        @error('form.module') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo <span class="text-rose-500">*</span></label>
                        <select wire:model="form.type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                            @foreach($types as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Orden</label>
                        <input type="number" wire:model="form.sort_order" min="0"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                        @error('form.sort_order') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Resumen</label>
                    <input type="text" wire:model="form.summary" placeholder="Una línea que explique de qué trata"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                    @error('form.summary') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Contenido <span class="text-rose-500">*</span></label>
                    <textarea wire:model="form.content" rows="16" placeholder="Se escribe en Markdown: ## para títulos, - para listas, **negrita**, `código`."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-xs leading-relaxed @error('form.content') border-rose-400 bg-rose-50 @enderror"></textarea>
                    <p class="mt-1 text-xs text-slate-500">Markdown: <code>##</code> títulos, <code>1.</code> pasos, <code>-</code> viñetas, <code>**negrita**</code>, <code>`código`</code>, <code>&gt;</code> cita.</p>
                    @error('form.content') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="form.published" class="custom-checkbox">
                    Publicada (si no, queda como borrador)
                </label>
            </div>

            <div class="flex shrink-0 items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-6">
                <button type="button" wire:click="closeModal" class="btn btn-secondary btn-sm">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary btn-sm">
                    <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Guardar cambios' : 'Crear guía' }}</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
