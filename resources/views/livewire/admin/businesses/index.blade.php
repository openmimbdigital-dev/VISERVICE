<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ org_term('Negocios') }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">{{ org_term('Negocios') }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Negocios registrados</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    @if($shows_all)
                        Todos los comercios registrados en SouulBi.
                    @else
                        Negocios asignados a tu cuenta de usuario.
                    @endif
                </p>
            </div>
            @can('businesses.create')
            <x-ui.create-button :href="route('admin.businesses.form')" class="w-full justify-center sm:w-auto">
                Nuevo negocio
            </x-ui.create-button>
            @endcan
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04] sm:p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-100">
                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                <p class="text-xs text-slate-500">Total de comercios</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04] sm:p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100">
                <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold text-slate-900">{{ $stats['active'] }}</p>
                <p class="text-xs text-slate-500">Activos</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04] sm:p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold text-slate-900">{{ $stats['pending'] }}</p>
                <p class="text-xs text-slate-500">Con pago pendiente</p>
            </div>
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <div class="relative w-full min-w-0 sm:min-w-48 sm:flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por nombre o NIT..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <select wire:model.live="filter_type"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 sm:w-auto">
                <option value="">Todos los tipos</option>
                @foreach($organization_types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filter_subscription"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 sm:w-auto">
                <option value="">Todas las suscripciones</option>
                <option value="pending">Pago pendiente</option>
                <option value="active">Activa</option>
                <option value="trial">En prueba</option>
                <option value="none">Sin suscripción</option>
            </select>
            <select wire:model.live="filter_status"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 sm:w-auto">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado</h2>
        </div>
        @if($businesses->isEmpty())
        <div class="px-4 py-16 text-center sm:px-5">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="mt-3 text-sm font-medium text-slate-900">No se encontraron comercios</p>
            <p class="mt-1 text-xs text-slate-500">Prueba ajustando los filtros de búsqueda.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-5">Comercio</th>
                        <th class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:table-cell sm:px-5">Tipo</th>
                        <th class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell sm:px-5">Suscripción</th>
                        <th class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell sm:px-5">Usuarios</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-5">Estado</th>
                        <th class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell sm:px-5">Registro</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-5">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($businesses as $business)
                    <tr class="transition hover:bg-slate-50/70">
                        <td class="px-3 py-4 sm:px-5">
                            <div class="flex min-w-0 items-center gap-3">
                                <x-ui.business-logo :business="$business" size="md" class="rounded-xl" />
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $business->name }}</p>
                                    <p class="truncate text-xs text-slate-400">NIT: {{ $business->nit }}</p>
                                    @if($business->city)
                                        <p class="truncate text-xs text-slate-400">{{ $business->city->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="hidden px-3 py-4 sm:table-cell sm:px-5">
                            <span class="text-sm text-slate-600">{{ $business->organization_type?->name ?? '—' }}</span>
                        </td>
                        <td class="hidden px-3 py-4 md:table-cell sm:px-5">
                            @php $sub = $business->latestSubscription; @endphp
                            @if($sub)
                                @php
                                    $color = match($sub->status) {
                                        'active'   => 'emerald',
                                        'trial'    => 'blue',
                                        'pending'  => 'amber',
                                        'past_due' => 'yellow',
                                        'cancelled'=> 'slate',
                                        'expired'  => 'red',
                                        default    => 'slate',
                                    };
                                @endphp
                                <div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $color }}-100 px-2.5 py-1 text-xs font-medium text-{{ $color }}-700">
                                        {{ $sub->status_label }}
                                    </span>
                                    @if($sub->plan)
                                        <p class="mt-1 text-xs text-slate-500">{{ $sub->plan->name }}</p>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs italic text-slate-400">Sin suscripción</span>
                            @endif
                        </td>
                        <td class="hidden px-3 py-4 md:table-cell sm:px-5">
                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span class="text-sm text-slate-700">{{ $business->users->count() }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-4 sm:px-5">
                            @canany(['businesses.activate', 'businesses.deactivate'])
                            <button wire:click="toggleStatus({{ $business->id }})" type="button">
                                @if($business->status)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 transition hover:bg-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500 transition hover:bg-slate-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Inactivo
                                    </span>
                                @endif
                            </button>
                            @else
                                @if($business->status)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Inactivo
                                    </span>
                                @endif
                            @endcanany
                        </td>
                        <td class="hidden px-3 py-4 lg:table-cell sm:px-5">
                            <p class="text-sm text-slate-700">{{ $business->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-400">{{ $business->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-3 py-4 sm:px-5">
                            <div class="flex flex-wrap items-center justify-end gap-1">
                                @if($business->latestSubscription?->status === 'pending')
                                    @role('superAdmin')
                                    <a href="{{ route('admin.payments.index') }}" wire:navigate
                                        class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2 py-1.5 text-xs font-medium text-amber-700 transition hover:bg-amber-100 sm:px-2.5"
                                        title="Ver pago pendiente">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="hidden sm:inline">Pago pendiente</span>
                                    </a>
                                    @endrole
                                @endif
                                @can('businesses.view')
                                <a href="{{ route('admin.businesses.show', $business) }}" wire:navigate
                                    class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2 py-1.5 text-xs font-medium text-indigo-700 transition hover:bg-indigo-100 sm:px-2.5"
                                    title="Ver">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span class="hidden sm:inline">Ver</span>
                                </a>
                                @endcan
                                @can('businesses.edit')
                                <a href="{{ route('admin.businesses.form.edit', $business) }}" wire:navigate
                                    class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-200 sm:px-2.5"
                                    title="Editar">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span class="hidden sm:inline">Editar</span>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($businesses->hasPages())
        <div class="border-t border-slate-100 px-4 py-4 sm:px-5">
            {{ $businesses->links() }}
        </div>
        @endif
        @endif
    </section>
</div>
