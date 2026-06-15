<div class="p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Roles y Permisos</h1>
            <p class="text-sm text-slate-500 mt-1">Define roles del sistema y controla qué puede hacer cada uno.</p>
        </div>
        <button wire:click="openCreate" type="button"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo rol
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Roles</p>
            <p class="text-3xl font-bold text-slate-900">{{ $roles->count() }}</p>
            <p class="text-xs text-slate-400 mt-1">Definidos en el sistema</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Permisos</p>
            <p class="text-3xl font-bold text-slate-900">{{ $totalPerms }}</p>
            <p class="text-xs text-slate-400 mt-1">Disponibles en el sistema</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm col-span-2 sm:col-span-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Usuarios asignados</p>
            <p class="text-3xl font-bold text-slate-900">{{ $roles->sum('users_count') }}</p>
            <p class="text-xs text-slate-400 mt-1">Con algún rol activo</p>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="max-w-sm">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar rol..."
                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
        </div>
    </div>

    {{-- Lista de roles --}}
    <div class="space-y-3">
        @foreach($roles as $role)
        @php
            $isProtected  = in_array($role->name, ['superAdmin', 'Comercio']);
            $isSuperAdmin = $role->name === 'superAdmin';
            $isExpanded   = $expandedRole === $role->id;

            $roleColors = [
                'superAdmin'    => ['bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'border' => 'border-violet-200', 'dot' => 'bg-violet-500'],
                'Comercio'      => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                'Administrador' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'dot' => 'bg-indigo-500'],
                'Supervisor'    => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
                'Operador'      => ['bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'border' => 'border-sky-200', 'dot' => 'bg-sky-500'],
            ];
            $color = $roleColors[$role->name] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'dot' => 'bg-slate-500'];
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

            {{-- Cabecera del rol --}}
            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl {{ $color['bg'] }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-slate-900 text-sm">{{ $role->name }}</span>
                            @if($isProtected)
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium {{ $color['bg'] }} {{ $color['text'] }} px-2 py-0.5 rounded-full border {{ $color['border'] }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Protegido
                            </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-0.5">
                            <span class="text-xs text-slate-400">
                                @if($isSuperAdmin)
                                    Acceso completo al sistema
                                @else
                                    {{ $role->permissions_count }} {{ $role->permissions_count == 1 ? 'permiso' : 'permisos' }}
                                @endif
                            </span>
                            <span class="text-slate-200 text-xs">·</span>
                            <span class="text-xs text-slate-400">
                                {{ $role->users_count }} {{ $role->users_count == 1 ? 'usuario' : 'usuarios' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{-- Toggle permisos --}}
                    @if(!$isSuperAdmin)
                    <button wire:click="toggleExpand({{ $role->id }})" type="button"
                        class="inline-flex items-center gap-1.5 text-xs font-medium rounded-lg px-3 py-1.5 transition
                            {{ $isExpanded ? 'bg-slate-800 text-white' : 'text-slate-600 bg-slate-100 hover:bg-slate-200' }}">
                        <svg class="w-3.5 h-3.5 transition-transform duration-200 {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        Permisos
                    </button>
                    @endif

                    {{-- Editar --}}
                    <button wire:click="openEdit({{ $role->id }})" type="button"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg px-3 py-1.5 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ $isSuperAdmin ? 'Ver' : 'Editar' }}
                    </button>

                    {{-- Eliminar --}}
                    @if(!$isProtected)
                    <button wire:click="delete({{ $role->id }})"
                        wire:confirm="¿Seguro que deseas eliminar el rol «{{ $role->name }}»?"
                        type="button"
                        @class([
                            'inline-flex items-center gap-1.5 text-xs font-medium rounded-lg px-3 py-1.5 transition',
                            'text-red-600 bg-red-50 hover:bg-red-100' => $role->users_count === 0,
                            'text-slate-400 bg-slate-100 cursor-not-allowed' => $role->users_count > 0,
                        ])
                        @if($role->users_count > 0) title="Tiene usuarios asignados" @endif>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eliminar
                    </button>
                    @endif
                </div>
            </div>

            {{-- Panel de permisos expandible --}}
            @if($isExpanded && !$isSuperAdmin)
            <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                @if($role->permissions_count === 0)
                    <p class="text-sm text-slate-400 italic">Sin permisos asignados.</p>
                @else
                @php
                    $permsByModule = [];
                    foreach ($modules as $moduleKey => $module) {
                        $permsInModule = $role->permissions->filter(
                            fn ($p) => array_key_exists($p->name, $module['permissions'])
                        );
                        if ($permsInModule->isNotEmpty()) {
                            $permsByModule[$moduleKey] = ['module' => $module, 'perms' => $permsInModule];
                        }
                    }
                    // Permisos que no están en ningún módulo
                    $allModulePerms = collect($modules)->flatMap(fn ($m) => array_keys($m['permissions']))->all();
                    $otherPerms = $role->permissions->filter(fn ($p) => !in_array($p->name, $allModulePerms));
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($permsByModule as $data)
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">{{ $data['module']['name'] }}</p>
                        <div class="space-y-1">
                            @foreach($data['perms'] as $perm)
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-xs text-slate-700">{{ $data['module']['permissions'][$perm->name] ?? $perm->name }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    @if($otherPerms->isNotEmpty())
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Otros</p>
                        <div class="space-y-1">
                            @foreach($otherPerms as $perm)
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-xs text-slate-700">{{ $perm->name }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endif

        </div>
        @endforeach

        @if($roles->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 py-14 text-center">
            <p class="text-sm text-slate-400">No se encontraron roles.</p>
        </div>
        @endif
    </div>

    {{-- Modal Crear / Editar --}}
    @if($showModal)
    @php
        $editingRole      = $selected_id ? \Spatie\Permission\Models\Role::find($selected_id) : null;
        $isSuper          = $editingRole?->name === 'superAdmin';
        $isProtected      = in_array($editingRole?->name, ['superAdmin', 'Comercio']); // nombre no editable
        $isPermsProtected = $isSuper; // permisos bloqueados solo para superAdmin
    @endphp
    <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-16 bg-black/50 backdrop-blur-sm"
        x-on:keydown.escape.window="$wire.closeModal()">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden max-h-[85vh] flex flex-col">

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-bold text-slate-900">
                    @if(!$selected_id) Nuevo rol
                    @elseif($isSuper) Ver rol: superAdmin
                    @else Editar rol: {{ $editingRole?->name }}
                    @endif
                </h3>
                <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-5 overflow-y-auto flex-1">

                {{-- Nombre del rol --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Nombre del rol <span class="text-red-500">*</span>
                    </label>
                    @if($isProtected)
                        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span class="text-sm text-slate-600 font-medium">{{ $name }}</span>
                            <span class="ml-auto text-xs text-slate-400">Rol protegido del sistema</span>
                        </div>
                    @else
                        <input wire:model="name" type="text" placeholder="Ej: Auditor"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('name') border-red-400 bg-red-50 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @endif
                </div>

                {{-- Permisos --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-medium text-slate-700">Permisos</label>
                        @if(!$isSuper)
                        <div class="flex items-center gap-2">
                            <button type="button"
                                wire:click="$set('selectedPerms', {{ json_encode($allPermissions->pluck('name')->toArray()) }})"
                                class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                                Seleccionar todos
                            </button>
                            <span class="text-slate-200">·</span>
                            <button type="button"
                                wire:click="$set('selectedPerms', [])"
                                class="text-xs text-slate-500 hover:text-slate-700 font-medium">
                                Limpiar
                            </button>
                        </div>
                        @endif
                    </div>

                    @if($isSuper)
                        <div class="flex items-center gap-3 rounded-xl bg-violet-50 border border-violet-100 px-4 py-3">
                            <svg class="w-5 h-5 text-violet-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <p class="text-sm text-violet-800">El rol <strong>superAdmin</strong> tiene acceso completo a todo el sistema. Sus permisos no se gestionan individualmente.</p>
                        </div>
                    @else
                        <div class="space-y-5 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                            @foreach($modules as $moduleKey => $module)
                            @php
                                $modulePermNames = array_keys($module['permissions']);
                                $moduleSelected  = count(array_intersect($modulePermNames, $selectedPerms));
                                $moduleTotal     = count($modulePermNames);
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        {{ $module['name'] }}
                                    </p>
                                    <span class="text-[11px] text-slate-400">{{ $moduleSelected }}/{{ $moduleTotal }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                    @foreach($module['permissions'] as $permName => $permLabel)
                                    <label class="flex items-center gap-2.5 rounded-lg px-3 py-2 cursor-pointer transition
                                        {{ in_array($permName, $selectedPerms) ? 'bg-indigo-50 border border-indigo-100' : 'bg-white border border-slate-100 hover:border-slate-200 hover:bg-slate-50' }}
                                        {{ $isPermsProtected ? 'opacity-70 cursor-default' : '' }}">
                                        <input type="checkbox"
                                            wire:model="selectedPerms"
                                            value="{{ $permName }}"
                                            {{ $isPermsProtected ? 'disabled' : '' }}
                                            class="w-3.5 h-3.5 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                        <span class="text-xs text-slate-700 leading-tight">{{ $permLabel }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 shrink-0">
                <button wire:click="closeModal" type="button"
                    class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    {{ $isSuper ? 'Cerrar' : 'Cancelar' }}
                </button>
                @if(!$isSuper)
                <button wire:click="save" type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $selected_id ? 'Guardar cambios' : 'Crear rol' }}
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
