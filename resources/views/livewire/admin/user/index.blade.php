<div class="relative mx-auto w-full max-w-[90rem] space-y-6 p-4 sm:p-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold text-slate-900">Usuarios</h1>
            <p class="mt-1 text-sm text-slate-500">
                @role('superAdmin')
                    Todos los usuarios del sistema.
                @else
                    Usuarios de tu comercio.
                @endrole
            </p>
        </div>
        @can('users.create')
        <x-ui.create-button wire:click="openCreate" class="w-full justify-center sm:w-auto">
            Nuevo usuario
        </x-ui.create-button>
        @endcan
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                <p class="text-xs text-slate-500">Total de usuarios</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $stats['active'] }}</p>
                <p class="text-xs text-slate-500">Activos</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-sky-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $stats['this_month'] }}</p>
                <p class="text-xs text-slate-500">Nuevos este mes</p>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <div class="relative w-full min-w-0 sm:min-w-48 sm:flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por nombre, correo o usuario..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
            </div>
            <select wire:model.live="filter_role"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 sm:w-auto">
                <option value="">Todos los roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filter_status"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 sm:w-auto">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        @if($users->isEmpty())
        <div class="py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="mt-3 text-sm font-medium text-slate-900">No se encontraron usuarios</p>
            <p class="mt-1 text-xs text-slate-500">Prueba ajustando los filtros o crea un nuevo usuario.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-5">Usuario</th>
                        @role('superAdmin')
                        <th class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell sm:px-5">Comercio</th>
                        @endrole
                        <th class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:table-cell sm:px-5">Rol</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-5">Estado</th>
                        <th class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell sm:px-5">Registro</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-5">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $user)
                    @php
                        $isMe      = $user->id === auth()->id();
                        $isPrimary = in_array($user->id, $primaryUserIds);
                        $isSuperA  = $user->hasRole('superAdmin');
                        $deletable = !$isMe && !$isPrimary && !$isSuperA;
                        $roleName  = $user->getRoleNames()->first() ?? '—';
                        // Un Comercio no puede desactivar al usuario principal (solo el propio superAdmin puede)
                        $canToggle = ! $isMe && ! $isSuperA && (! $isPrimary || auth()->user()->hasRole('superAdmin'));
                        $canToggleStatus = $canToggle && (
                            ($user->status && auth()->user()->can('users.deactivate'))
                            || (! $user->status && auth()->user()->can('users.activate'))
                        );
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        {{-- Nombre + email --}}
                        <td class="px-3 py-4 sm:px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-white">
                                        {{ strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $user->full_name }}
                                        @if($isMe)
                                            <span class="ml-1 text-[10px] font-medium text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded-full">Tú</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                    @if($user->username)
                                        <p class="text-xs text-slate-400">{{ $user->username }}</p>
                                    @endif
                                    <p class="mt-1 text-xs text-slate-500 sm:hidden">{{ $roleName }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Comercio (solo superAdmin) --}}
                        @role('superAdmin')
                        <td class="hidden px-3 py-4 md:table-cell sm:px-5">
                            <span class="text-sm text-slate-600">{{ $user->business?->name ?? '—' }}</span>
                        </td>
                        @endrole

                        {{-- Rol --}}
                        <td class="hidden px-3 py-4 sm:table-cell sm:px-5">
                            @php
                                $roleBg = match($roleName) {
                                    'superAdmin'     => 'bg-rose-100 text-rose-700',
                                    'Comercio'       => 'bg-violet-100 text-violet-700',
                                    'Administrador'  => 'bg-indigo-100 text-indigo-700',
                                    'Supervisor'     => 'bg-sky-100 text-sky-700',
                                    'Operador'       => 'bg-slate-100 text-slate-600',
                                    default          => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $roleBg }}">
                                {{ $roleName }}
                            </span>
                            @if($isPrimary)
                                <p class="text-[10px] text-violet-500 mt-0.5">Principal</p>
                            @endif
                        </td>

                        {{-- Estado --}}
                        <td class="px-3 py-4 sm:px-5">
                            @if(! $canToggleStatus)
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full
                                    {{ $user->status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->status ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $user->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            @else
                                <button wire:click="toggleStatus({{ $user->id }})" type="button"
                                    title="{{ $user->status ? 'Clic para desactivar' : 'Clic para activar' }}">
                                    @if($user->status)
                                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full hover:bg-emerald-200 transition cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full hover:bg-slate-200 transition cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Inactivo
                                        </span>
                                    @endif
                                </button>
                            @endif
                        </td>

                        {{-- Fecha --}}
                        <td class="hidden px-3 py-4 lg:table-cell sm:px-5">
                            <p class="text-sm text-slate-700">{{ $user->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-400">{{ $user->created_at->diffForHumans() }}</p>
                        </td>

                        {{-- Acciones --}}
                        <td class="px-3 py-4 sm:px-5">
                            <div class="flex flex-wrap items-center justify-end gap-1">
                                @can('users.edit')
                                <button wire:click="openEdit({{ $user->id }})" type="button"
                                    class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-indigo-50 hover:text-indigo-700 sm:px-2.5"
                                    title="Editar usuario">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span class="hidden sm:inline">Editar</span>
                                </button>
                                @endcan

                                @can('users.delete')
                                @if($deletable)
                                <button
                                    wire:click="delete({{ $user->id }})"
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2 py-1.5 text-xs font-medium text-rose-600 transition hover:bg-rose-100 hover:text-rose-700 sm:px-2.5"
                                    title="Eliminar usuario">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span class="hidden sm:inline">Eliminar</span>
                                </button>
                                @else
                                <span class="inline-flex cursor-not-allowed items-center gap-1 rounded-lg px-2 py-1.5 text-xs text-slate-300 sm:px-2.5"
                                    title="{{ $isMe ? 'No puedes eliminarte a ti mismo' : ($isPrimary ? 'No se puede eliminar al usuario principal del comercio' : 'No se puede eliminar') }}">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span class="hidden sm:inline">Protegido</span>
                                </span>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="border-t border-slate-100 px-4 py-4 sm:px-5">
            {{ $users->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Modal crear / editar --}}
    @if($showModal)
    <x-ui.modal centered>
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
                <h3 class="text-base font-semibold text-slate-900">
                    {{ $editing ? 'Editar usuario' : 'Nuevo usuario' }}
                </h3>
                <button wire:click="closeModal" type="button"
                    class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
                <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">

                {{-- Nombre y apellido --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Nombre <span class="text-rose-500">*</span></label>
                        <input wire:model="first_name" type="text" placeholder="Juan"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('first_name') border-rose-400 bg-rose-50 @enderror">
                        @error('first_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Apellido <span class="text-rose-500">*</span></label>
                        <input wire:model="last_name" type="text" placeholder="Pérez"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('last_name') border-rose-400 bg-rose-50 @enderror">
                        @error('last_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Correo --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Correo electrónico <span class="text-rose-500">*</span></label>
                    <input wire:model="email" type="email" placeholder="juan@ejemplo.com"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('email') border-rose-400 bg-rose-50 @enderror">
                    @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                {{-- Usuario y teléfono --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Nombre de usuario <span class="text-rose-500">*</span></label>
                        <input wire:model="username" type="text" placeholder="juanperez"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('username') border-rose-400 bg-rose-50 @enderror">
                        @error('username') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input wire:model="phone_number" type="tel" placeholder="300 123 4567"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                </div>

                {{-- Contraseña --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">
                            Contraseña {{ $editing ? '(dejar vacío para no cambiar)' : '*' }}
                        </label>
                        <input wire:model="password" type="password" placeholder="Mínimo 8 caracteres"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('password') border-rose-400 bg-rose-50 @enderror">
                        @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Confirmar contraseña</label>
                        <input wire:model="password_confirmation" type="password" placeholder="Repite la contraseña"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                </div>

                {{-- Rol (solo superAdmin) y estado --}}
                <div class="{{ auth()->user()->hasRole('superAdmin') ? 'grid grid-cols-1 gap-4 sm:grid-cols-2' : '' }}">
                    @role('superAdmin')
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Rol <span class="text-rose-500">*</span></label>
                        <select wire:model="role"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('role') border-rose-400 bg-rose-50 @enderror">
                            <option value="">Selecciona un rol</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    @endrole
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Estado</label>
                        @if(! $editing || auth()->user()->can('users.activate') || auth()->user()->can('users.deactivate'))
                        <div class="flex items-center gap-3 mt-2.5">
                            <button type="button" wire:click="$toggle('status')"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200
                                    {{ $status ? 'bg-indigo-600' : 'bg-slate-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200
                                    {{ $status ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                            <span class="text-sm {{ $status ? 'text-emerald-700 font-medium' : 'text-slate-500' }}">
                                {{ $status ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        @else
                        <div class="mt-2.5">
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full
                                {{ $status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $status ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        @endif
                        @if(!auth()->user()->hasRole('superAdmin') && !$editing)
                            <p class="text-[11px] text-slate-400 mt-1.5">El rol asignado será <span class="font-medium text-violet-600">Comercio</span> automáticamente.</p>
                        @endif
                    </div>
                </div>

                </div>

                {{-- Botones --}}
                <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                    <button type="button" wire:click="closeModal"
                        class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 hover:text-slate-800 sm:w-auto">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto"
                        wire:loading.attr="disabled">
                        <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5 5.373 5 12h4z"/></svg>
                        <span wire:loading.remove>{{ $editing ? 'Guardar cambios' : 'Crear usuario' }}</span>
                        <span wire:loading>Guardando...</span>
                    </button>
                </div>
            </form>
    </x-ui.modal>
    @endif
</div>
