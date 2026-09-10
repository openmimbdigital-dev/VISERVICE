<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Presentación</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Usuarios</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Acceso</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Usuarios</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Usuarios de demostración del colegio. Los nuevos registros solo viven en esta sesión.</p>
            </div>
            <x-ui.create-button wire:click="openCreate" class="w-full justify-center sm:w-auto">
                Nuevo usuario
            </x-ui.create-button>
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Usuarios</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-slate-900 sm:text-3xl">{{ count($users) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Activos</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-indigo-600 sm:text-3xl">{{ collect($users)->where('status', 'active')->count() }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5">Usuario</th>
                        <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Rol</th>
                        <th class="px-3 py-3 sm:px-5">Estado</th>
                        <th class="px-3 py-3 sm:px-5"><span class="sr-only">Ver</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $row)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-4 sm:px-5">
                            <p class="font-medium text-slate-900">{{ $row['name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $row['email'] }}</p>
                            <p class="mt-1 text-xs text-slate-500 sm:hidden">{{ $row['role'] }}</p>
                        </td>
                        <td class="hidden px-3 py-4 text-slate-700 sm:table-cell sm:px-5">{{ $row['role'] }}</td>
                        <td class="px-3 py-4 sm:px-5">
                            <span @class([
                                'inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1',
                                'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $row['status'] === 'active',
                                'bg-slate-100 text-slate-600 ring-slate-500/20' => $row['status'] !== 'active',
                            ])>{{ $row['status'] === 'active' ? 'Activo' : 'Inactivo' }}</span>
                        </td>
                        <td class="px-3 py-4 text-right sm:px-5">
                            <a href="{{ route('admin.presentation.users.show', $row['id']) }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @if($showModal)
    <x-ui.modal centered maxWidth="lg">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">Nuevo usuario</h3>
            <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="name"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('name') border-rose-400 bg-rose-50 @enderror">
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Correo <span class="text-rose-500">*</span></label>
                    <input type="email" wire:model="email"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('email') border-rose-400 bg-rose-50 @enderror">
                    @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Rol <span class="text-rose-500">*</span></label>
                        <select wire:model="role"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('role') border-rose-400 bg-rose-50 @enderror">
                            @foreach($roles as $role_name)
                            <option value="{{ $role_name }}">{{ $role_name }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                        <select wire:model="status"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closeModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">Guardar</button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
