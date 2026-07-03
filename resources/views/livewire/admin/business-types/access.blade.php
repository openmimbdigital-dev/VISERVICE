<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.businesses.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Negocios</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.business-types.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Tipos de negocio</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Acceso por negocio</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Acceso por negocio</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Elige el tipo, selecciona negocio(s) y asigna roles y permisos. Con un solo negocio se cargan sus asignaciones actuales.
                </p>
            </div>
            <a href="{{ route('admin.business-types.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Tipos de negocio</a>
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
                <p class="font-semibold text-indigo-900">¿Para qué sirve Acceso por negocio?</p>
                <p class="text-indigo-900/90">
                    Aquí defines el <strong>catálogo de roles y permisos</strong> que cada negocio puede usar al gestionar usuarios en <em>Roles y permisos</em>. Es la <strong>capa 1</strong> del control de acceso (permisos Spatie).
                </p>
                <ul class="list-disc space-y-2 pl-5 text-indigo-900/90">
                    <li><strong>Roles disponibles:</strong> qué roles podrá ver y asignar el administrador del negocio al crear o editar usuarios.</li>
                    <li><strong>Permisos disponibles:</strong> qué permisos aparecerán al configurar esos roles.</li>
                </ul>
                <p class="rounded-xl border border-indigo-200/60 bg-white/60 px-3 py-2.5 text-xs text-indigo-800">
                    Esto <strong>no sustituye</strong> los permisos del usuario ni bloquea rutas por sí solo: el acceso real lo define el rol asignado a cada usuario. Para habilitar u ocultar módulos del menú lateral por negocio, usa <em>Módulos por negocio</em> (capa 2). Con varios negocios seleccionados, solo se marcan los roles y permisos que comparten todos.
                </p>
            </div>
        </div>
    </aside>

    {{-- Tipo de negocio --}}
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Tipo de negocio</h2>
        </div>
        <div class="p-4 sm:p-5">
            <label class="mb-1.5 block text-xs font-medium text-slate-700">Filtrar negocios por tipo <span class="text-rose-500">*</span></label>
            <select wire:model.live="business_type_id"
                class="w-full max-w-md rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="">— Seleccionar —</option>
                @foreach($business_types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            @error('business_type_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
    </section>

    @if($business_type_id)
    {{-- Negocios --}}
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Negocios</h2>
            <p class="mt-1 text-xs text-slate-500">Marca uno o más negocios. Al seleccionar uno verás sus roles y permisos actuales.</p>
        </div>
        <div class="p-4 sm:p-5">
            @if($businesses_for_type->isEmpty())
                <p class="text-sm text-slate-400 italic">No hay negocios activos de este tipo.</p>
            @else
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($businesses_for_type as $business)
                    <label @class([
                        'flex cursor-pointer items-start gap-3 rounded-xl border px-3 py-3 transition',
                        'border-indigo-300 bg-indigo-50/50' => in_array($business->id, $selected_business_ids, false) || in_array((string) $business->id, $selected_business_ids, true),
                        'border-slate-100 hover:bg-slate-50' => ! in_array($business->id, $selected_business_ids, false) && ! in_array((string) $business->id, $selected_business_ids, true),
                    ])>
                        <input type="checkbox" wire:model.live="selected_business_ids" value="{{ $business->id }}"
                            class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20">
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-slate-900">{{ $business->name }}</span>
                            @if($business->nit)
                                <span class="text-[11px] text-slate-400">NIT {{ $business->nit }}</span>
                            @endif
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
        <div class="text-sm text-slate-600">
            <p>
                Configurando
                <span class="font-semibold text-slate-900">{{ count($selected_business_ids) }}</span>
                negocio(s).
            </p>
            @if(count($selected_business_ids) === 1)
                <p class="mt-0.5 text-xs text-slate-500">Marcados los roles y permisos asignados a este negocio.</p>
            @else
                <p class="mt-0.5 text-xs text-slate-500">Marcados solo los roles y permisos que comparten todos los negocios seleccionados.</p>
            @endif
        </div>
        @can('business_types.access.manage')
        <button type="button" wire:click="save" wire:loading.attr="disabled"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
            <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Guardar asignación
        </button>
        @endcan
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Roles --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-800">Roles disponibles</h2>
                <p class="mt-1 text-xs text-slate-500">Roles que podrán gestionarse en Roles y permisos para este negocio.</p>
            </div>
            <div class="max-h-[28rem] space-y-2 overflow-y-auto p-4 sm:p-5">
                @forelse($roles as $role)
                    @php $isGlobal = in_array($role->name, config('permissions.global_roles', []), true); @endphp
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-100 px-3 py-3 transition hover:bg-slate-50">
                        <input type="checkbox" wire:model.live="selected_role_ids" value="{{ $role->id }}"
                            @disabled(! auth()->user()->can('business_types.access.manage'))
                            class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-50">
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-slate-900">{{ $role->name }}</span>
                            @if($isGlobal)<span class="text-[11px] text-emerald-600">Rol global</span>@endif
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-slate-400 italic">No hay roles configurables.</p>
                @endforelse
            </div>
        </section>

        {{-- Permisos --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-800">Permisos disponibles</h2>
                <p class="mt-1 text-xs text-slate-500">Permisos visibles al editar roles de este negocio.</p>
            </div>
            <div class="max-h-[28rem] space-y-4 overflow-y-auto p-4 sm:p-5">
                @foreach($modules as $module_key => $module)
                    @php
                        $module_perms = array_keys($module['permissions']);
                        $selected_count = collect($module_perms)->filter(fn ($p) => in_array($p, $selected_permissions, true))->count();
                        $all_selected = $selected_count === count($module_perms) && $module_perms !== [];
                    @endphp
                    <div class="rounded-xl border border-slate-100">
                        <button type="button" wire:click="toggleModule('{{ $module_key }}')"
                            @disabled(! auth()->user()->can('business_types.access.manage'))
                            class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60">
                            <span class="text-sm font-medium text-slate-900">{{ $module['name'] }}</span>
                            <span class="shrink-0 text-xs {{ $all_selected ? 'text-indigo-600' : 'text-slate-400' }}">
                                {{ $selected_count }}/{{ count($module_perms) }}
                            </span>
                        </button>
                        <div class="space-y-1 border-t border-slate-100 px-4 py-3">
                            @foreach($module['permissions'] as $perm_key => $perm_label)
                                <label class="flex cursor-pointer items-center gap-2 py-1">
                                    <input type="checkbox" wire:model.live="selected_permissions" value="{{ $perm_key }}"
                                        @disabled(! auth()->user()->can('business_types.access.manage'))
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-50">
                                    <span class="text-xs text-slate-600">{{ $perm_label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
    @else
    <div class="mb-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 px-4 py-8 text-center">
        <p class="text-sm text-slate-500">Selecciona al menos un negocio para ver y asignar roles y permisos.</p>
    </div>
    @endif

    {{-- Resumen persistente --}}
    <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Asignación actual por negocio</h2>
            <p class="mt-1 text-xs text-slate-500">
                @if($selected_type)
                    Negocios del tipo <span class="font-medium text-slate-700">{{ $selected_type->name }}</span>.
                @else
                    Selecciona un tipo de negocio para ver el resumen.
                @endif
            </p>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($assigned_businesses as $business)
            <div class="p-4 sm:p-5">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <h3 class="text-sm font-semibold text-slate-900">{{ $business->name }}</h3>
                    @if($business->status)
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">Activo</span>
                    @else
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">Inactivo</span>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Roles ({{ $business->roles->count() }})</p>
                        @if($business->roles->isEmpty())
                            <p class="text-xs text-slate-400 italic">Sin roles asignados.</p>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($business->roles as $role)
                                <span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">{{ $role->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Permisos ({{ $business->permissions->count() }})</p>
                        @if($business->permissions->isEmpty())
                            <p class="text-xs text-slate-400 italic">Sin permisos asignados.</p>
                        @else
                            <div class="flex max-h-32 flex-wrap gap-1 overflow-y-auto">
                                @foreach($business->permissions->sortBy('name') as $permission)
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] text-slate-600" title="{{ $permission->name }}">{{ $permission->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <p class="p-6 text-center text-sm text-slate-400">No hay negocios de este tipo.</p>
            @endforelse
        </div>
    </section>
    @endif
</div>
