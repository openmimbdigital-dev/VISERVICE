<div class="p-6 space-y-6">

    {{-- Encabezado --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Negocios Registrados</h1>
        <p class="text-sm text-slate-500 mt-1">Todos los comercios que se han registrado en VISERVICE.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                <p class="text-xs text-slate-500">Total de comercios</p>
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
            <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $stats['pending'] }}</p>
                <p class="text-xs text-slate-500">Con pago pendiente</p>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-48">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por nombre o NIT..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
            </div>
            <select wire:model.live="filter_type"
                class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                <option value="">Todos los tipos</option>
                @foreach($business_types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filter_subscription"
                class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                <option value="">Todas las suscripciones</option>
                <option value="pending">Pago pendiente</option>
                <option value="active">Activa</option>
                <option value="trial">En prueba</option>
                <option value="none">Sin suscripción</option>
            </select>
            <select wire:model.live="filter_status"
                class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        @if($businesses->isEmpty())
        <div class="py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="mt-3 text-sm font-medium text-slate-900">No se encontraron comercios</p>
            <p class="mt-1 text-xs text-slate-500">Prueba ajustando los filtros de búsqueda.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Comercio</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Suscripción</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Usuarios</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Registro</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($businesses as $business)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                {{-- Logo --}}
                                <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center">
                                    @if($business->logo)
                                        <img src="{{ Storage::disk('public')->url($business->logo) }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-sm font-bold text-slate-400">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $business->name }}</p>
                                    <p class="text-xs text-slate-400">NIT: {{ $business->nit }}</p>
                                    @if($business->city)
                                        <p class="text-xs text-slate-400">{{ $business->city->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-sm text-slate-600">{{ $business->business_type?->name ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-4">
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
                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full
                                        bg-{{ $color }}-100 text-{{ $color }}-700">
                                        {{ $sub->status_label }}
                                    </span>
                                    @if($sub->plan)
                                        <p class="text-xs text-slate-500 mt-1">{{ $sub->plan->name }}</p>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Sin suscripción</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span class="text-sm text-slate-700">{{ $business->users->count() }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <button wire:click="toggleStatus({{ $business->id }})" type="button">
                                @if($business->status)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full hover:bg-emerald-200 transition">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full hover:bg-slate-200 transition">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Inactivo
                                    </span>
                                @endif
                            </button>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm text-slate-700">{{ $business->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-400">{{ $business->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($business->latestSubscription?->status === 'pending')
                                    <a href="{{ route('admin.payments.index') }}" wire:navigate
                                        class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 px-2.5 py-1.5 rounded-lg transition"
                                        title="Ver pago pendiente">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Pago pendiente
                                    </a>
                                @endif
                                <a href="{{ route('admin.businesses.show', $business) }}" wire:navigate
                                    class="inline-flex items-center gap-1 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Ver detalle
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($businesses->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $businesses->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
