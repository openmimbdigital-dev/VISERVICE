<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.teams.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Gestión de eventos</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.teams.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Equipo de evento</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $form->isEditing() ? 'Editar' : 'Nuevo' }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Gestión de eventos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                    {{ $form->isEditing() ? 'Editar equipo de evento' : 'Nuevo equipo de evento' }}
                </h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Define el equipo, los roles que lo integran y las personas asignadas.</p>
            </div>
            <a href="{{ route('admin.events.teams.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Volver</a>
        </div>
    </header>

    <form wire:submit="save" class="space-y-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-800">Información del equipo</h2>
            </div>
            <div class="space-y-5 p-4 sm:p-5">
                @if($is_super_admin)
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Iglesia <span class="text-rose-500">*</span></label>
                        <select wire:model.live="form.business_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.business_id') border-rose-400 bg-rose-50 @enderror">
                            <option value="">Selecciona una iglesia</option>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                        @error('form.business_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
                        <input wire:model="form.name" type="text" placeholder="Ej. Equipo de culto dominical"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.name') border-rose-400 bg-rose-50 @enderror">
                        @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <div class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                            <div>
                                <p class="text-xs font-medium text-slate-700">Estado</p>
                                <p class="text-sm text-slate-600">{{ $form->active ? 'Activo' : 'Inactivo' }}</p>
                            </div>
                            <button type="button" wire:click="$toggle('form.active')"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500/30 {{ $form->active ? 'bg-indigo-600' : 'bg-slate-300' }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $form->active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Descripción</label>
                    <textarea wire:model="form.description" rows="3" placeholder="Describe la responsabilidad del equipo"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.description') border-rose-400 bg-rose-50 @enderror"></textarea>
                    @error('form.description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-800">Roles del equipo</h2>
                <p class="mt-1 text-xs text-slate-500">Selecciona los roles que integran este equipo.</p>
            </div>
            <div class="p-4 sm:p-5">
                @if($roles->isEmpty())
                    <div class="rounded-xl border border-amber-100 bg-amber-50 px-3.5 py-3 text-sm text-amber-800">
                        <p>No hay roles disponibles para esta iglesia.</p>
                        @can('events.team_roles.create')
                            <a href="{{ route('admin.events.team-roles.create') }}" wire:navigate class="mt-2 inline-flex font-medium text-amber-900 underline underline-offset-2 hover:text-amber-950">
                                Crear un rol del equipo
                            </a>
                        @else
                            <p class="mt-1 text-xs">Pide a un administrador que cree roles en <strong>Gestión de eventos → Roles del equipo</strong>.</p>
                        @endcan
                    </div>
                @else
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs text-slate-500">Marca los roles que formarán parte de este equipo.</p>
                        @can('events.team_roles.create')
                            <a href="{{ route('admin.events.team-roles.create') }}" wire:navigate class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                                + Nuevo rol
                            </a>
                        @endcan
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($roles as $role)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                                <input type="checkbox" wire:model.live="form.role_ids" value="{{ $role->id }}"
                                    class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-slate-800">{{ $role->name }}</span>
                                    @if($role->functions)
                                        <span class="mt-0.5 block text-xs text-slate-500">{{ str($role->functions)->limit(90) }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
                @error('form.role_ids') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                @error('form.role_ids.*') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div>
                    <h2 class="font-semibold text-slate-800">Integrantes</h2>
                    <p class="mt-1 text-xs text-slate-500">Asigna usuarios a los roles del equipo.</p>
                </div>
                <button type="button" wire:click="addMember" @disabled(empty($form->role_ids))
                    class="btn btn-outline-secondary btn-sm w-full justify-center disabled:opacity-50 sm:w-auto">
                    Agregar integrante
                </button>
            </div>
            <div class="space-y-3 p-4 sm:p-5">
                @forelse($form->members as $index => $member)
                    <div class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-12 sm:items-start" wire:key="member-{{ $index }}">
                        <div class="sm:col-span-5">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Usuario <span class="text-rose-500">*</span></label>
                            <select wire:model="form.members.{{ $index }}.user_id"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.members.'.$index.'.user_id') border-rose-400 bg-rose-50 @enderror">
                                <option value="">Selecciona un usuario</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                @endforeach
                            </select>
                            @error('form.members.'.$index.'.user_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-5">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Rol <span class="text-rose-500">*</span></label>
                            <select wire:model="form.members.{{ $index }}.event_team_role_id"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.members.'.$index.'.event_team_role_id') border-rose-400 bg-rose-50 @enderror">
                                <option value="">Selecciona un rol</option>
                                @foreach($roles->whereIn('id', $form->role_ids) as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('form.members.'.$index.'.event_team_role_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end sm:col-span-2">
                            <button type="button" wire:click="removeMember({{ $index }})"
                                class="w-full rounded-xl bg-rose-50 px-3 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-100">
                                Quitar
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Aún no hay integrantes asignados.</p>
                @endforelse
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.events.teams.index') }}" wire:navigate class="btn btn-outline-secondary w-full justify-center sm:w-auto">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary w-full justify-center disabled:opacity-60 sm:w-auto">
                <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Guardar cambios' : 'Crear equipo' }}</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>
