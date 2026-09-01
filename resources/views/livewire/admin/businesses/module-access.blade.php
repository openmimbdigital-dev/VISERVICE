<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.businesses.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Negocios</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Módulos por negocio</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Módulos por negocio</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Asigna módulos (secciones del menú) y submódulos (ítems) a cada negocio. Los hijos heredan del negocio padre.
                </p>
            </div>
            <a href="{{ route('admin.businesses.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Ver negocios</a>
        </div>
    </header>

    <aside class="mb-8 overflow-hidden rounded-2xl border border-indigo-100 bg-indigo-50/70 ring-1 ring-indigo-600/10">
        <div class="flex gap-3 px-4 py-4 sm:px-5">
            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0 space-y-3 text-sm text-indigo-950">
                <p class="font-semibold text-indigo-900">¿Cómo funciona el acceso en SouulBi?</p>
                <p class="text-indigo-900/90">
                    El sistema valida el acceso en <strong>dos capas</strong>. Un usuario solo ve una pantalla o accede a una ruta cuando cumple <strong>ambas</strong>:
                </p>
                <ol class="list-decimal space-y-2 pl-5 text-indigo-900/90">
                    <li>
                        <strong>Capa 1 — Permisos Spatie:</strong> lo que el rol del usuario permite (p. ej. <code class="rounded bg-indigo-100/80 px-1 py-0.5 text-xs">workshop.clients.view</code>). Se gestiona en <em>Roles y permisos</em> y puede acotarse por negocio en <em>Acceso por negocio</em>.
                    </li>
                    <li>
                        <strong>Capa 2 — Módulos del menú (esta pantalla):</strong> qué secciones e ítems del menú lateral están habilitados para el <strong>negocio activo</strong> del usuario. Si el módulo no está asignado aquí, no aparece en el menú y la ruta queda bloqueada.
                    </li>
                </ol>
                <p class="rounded-xl border border-indigo-200/60 bg-white/60 px-3 py-2.5 text-xs text-indigo-800">
                    <strong>En esta pantalla</strong> defines la capa 2: marcas módulos (secciones) y submódulos (ítems) por negocio raíz. Las sucursales hijas <strong>heredan</strong> la configuración del padre. El superAdmin no está sujeto a esta capa. Solo <strong>Suscripciones</strong> es sección de plataforma. La sección <strong>Negocios</strong> y sus ítems (p. ej. <em>Cargos del equipo</em>) se asignan aquí.
                </p>
            </div>
        </div>
    </aside>

    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Tipo de organización</h2>
        </div>
        <div class="p-4 sm:p-5">
            <select wire:model.live="organization_type_id"
                class="w-full max-w-md rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="">— Seleccionar —</option>
                @foreach($organization_types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
    </section>

    @if($organization_type_id)
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Negocios raíz</h2>
            <p class="mt-1 text-xs text-slate-500">Solo negocios sin padre. Las sucursales hijas heredan estos módulos.</p>
        </div>
        <div class="p-4 sm:p-5">
            @if($businesses_for_type->isEmpty())
                <p class="text-sm text-slate-400 italic">No hay negocios raíz de este tipo.</p>
            @else
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($businesses_for_type as $business)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-100 px-3 py-3 transition hover:bg-slate-50">
                        <input type="checkbox" wire:model.live="selected_business_ids" value="{{ $business->id }}"
                            class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20">
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-slate-900">{{ $business->name }}</span>
                            @if($business->nit)<span class="text-[11px] text-slate-400">NIT {{ $business->nit }}</span>@endif
                        </span>
                    </label>
                    @endforeach
                </div>
            @endif
            @error('selected_business_ids') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
    </section>

    @if(count($selected_business_ids) > 0)
    <div class="mb-4 flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-600">Configurando <span class="font-semibold">{{ count($selected_business_ids) }}</span> negocio(s).</p>
        @role('superAdmin')
        <button type="button" wire:click="save" wire:loading.attr="disabled"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
            Guardar módulos
        </button>
        @endrole
    </div>

    <section class="mb-8 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Módulos y submódulos</h2>
            <p class="mt-1 text-xs text-slate-500">Marca la sección (módulo) y los ítems (submódulos) habilitados.</p>
        </div>
        <div class="divide-y divide-slate-100 p-4 sm:p-5">
            @forelse($assignable_sections as $section)
            <div class="py-4 first:pt-0 last:pb-0">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="checkbox" wire:model.live="selected_section_ids" value="{{ $section->id }}"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20">
                        <span class="text-sm font-semibold text-slate-900">{{ $section->name }}</span>
                    </label>
                    @if($section->items->isNotEmpty())
                    <button type="button" wire:click="toggleSection({{ $section->id }})"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                        Marcar / quitar todos
                    </button>
                    @endif
                </div>
                @if($section->items->isNotEmpty())
                <div class="mt-3 ml-7 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach($section->items as $item)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 hover:bg-slate-50">
                        <input type="checkbox" wire:model.live="selected_menu_item_ids" value="{{ $item->id }}"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20">
                        <span class="text-xs text-slate-700">{{ $item->name }}</span>
                    </label>
                    @endforeach
                </div>
                @else
                <p class="mt-2 ml-7 text-xs text-slate-400">Sección de enlace único (sin submódulos).</p>
                @endif
            </div>
            @empty
            <p class="py-4 text-sm text-slate-400 italic">No hay módulos asignables configurados.</p>
            @endforelse
        </div>
    </section>
    @else
    <div class="mb-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 px-4 py-8 text-center">
        <p class="text-sm text-slate-500">Selecciona un negocio para configurar módulos y submódulos.</p>
    </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Asignación actual</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($assigned_businesses as $business)
            <div class="p-4 sm:p-5">
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ $business->name }}</h3>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Módulos</p>
                        @if($business->menuSections->isEmpty())
                            <p class="text-xs text-slate-400 italic">Ninguno</p>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($business->menuSections as $section)
                                <span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">{{ $section->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Submódulos</p>
                        @if($business->menuItems->isEmpty())
                            <p class="text-xs text-slate-400 italic">Ninguno</p>
                        @else
                            <div class="flex max-h-32 flex-wrap gap-1 overflow-y-auto">
                                @foreach($business->menuItems as $item)
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] text-slate-600">{{ $item->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <p class="p-6 text-center text-sm text-slate-400">No hay negocios raíz de este tipo.</p>
            @endforelse
        </div>
    </section>
    @endif
</div>
