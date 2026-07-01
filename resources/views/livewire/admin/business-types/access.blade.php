<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.businesses.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Negocios</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.business-types.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Tipos de negocio</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Acceso por tipo</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Acceso por tipo de negocio</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Define qué roles y permisos estarán disponibles para cada tipo de negocio.
                </p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.business-types.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Tipos de negocio</a>
                @can('business_types.access.manage')
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex w-full flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto sm:flex-none">
                    <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Guardar
                </button>
                @endcan
            </div>
        </div>
    </header>

    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Tipo de negocio</h2>
        </div>
        <div class="p-4 sm:p-5">
            <label class="mb-1.5 block text-xs font-medium text-slate-700">Seleccionar tipo <span class="text-rose-500">*</span></label>
            <select wire:model.live="business_type_id"
                class="w-full max-w-md rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="">— Seleccionar —</option>
                @foreach($business_types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            @error('business_type_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            @if($selected_type)
                <p class="mt-2 text-xs text-slate-500">Configurando acceso para: <span class="font-medium text-slate-700">{{ $selected_type->name }}</span></p>
            @endif
        </div>
    </section>

    @if($business_type_id)
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Roles --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-800">Roles habilitados</h2>
                <p class="mt-1 text-xs text-slate-500">Solo estos roles podrán asignarse a usuarios de este tipo.</p>
            </div>
            <div class="max-h-[28rem] space-y-2 overflow-y-auto p-4 sm:p-5">
                @foreach($roles as $role)
                    @php
                        $isGlobal = in_array($role->name, config('permissions.global_roles', []), true);
                    @endphp
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-100 px-3 py-3 transition hover:bg-slate-50">
                        <input type="checkbox" wire:model="selected_role_ids" value="{{ $role->id }}"
                            @disabled(! auth()->user()->can('business_types.access.manage'))
                            class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-50">
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-slate-900">{{ $role->name }}</span>
                            @if($isGlobal)
                                <span class="text-[11px] text-emerald-600">Rol global</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- Permisos --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-800">Permisos habilitados</h2>
                <p class="mt-1 text-xs text-slate-500">Módulos y acciones visibles para usuarios de este tipo.</p>
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
                                    <input type="checkbox" wire:model="selected_permissions" value="{{ $perm_key }}"
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
    @endif
</div>
