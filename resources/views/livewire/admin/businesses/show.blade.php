@php
    $rep = is_array($business->representative) ? $business->representative : [];
@endphp

<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6" x-data="{ tab: 'info' }">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.businesses.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Negocios</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $business->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 flex-1 items-start gap-4">
                <x-ui.business-logo :business="$business" size="lg" class="rounded-2xl shadow-sm" />
                <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $business->name }}</h1>
                        @if($business->status)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Activo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Inactivo
                            </span>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
                        @if($business->nit)
                            <span>NIT: <span class="font-medium text-slate-700">{{ $business->nit }}</span></span>
                        @endif
                        @if($business->organization_type)
                            <span>Organización: <span class="font-medium text-slate-700">{{ $business->organization_type->name }}</span></span>
                        @endif
                        @if($business->business_type)
                            <span>Tipo: <span class="font-medium text-slate-700">{{ $business->business_type->name }}</span></span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.businesses.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                    Volver
                </a>
                @can('businesses.edit')
                <a href="{{ route('admin.businesses.form.edit', $business) }}" wire:navigate class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">
                    Editar
                </a>
                @endcan
                @canany(['businesses.activate', 'businesses.deactivate'])
                <button type="button" wire:click="toggleStatus" wire:confirm="{{ $business->status ? '¿Desactivar este negocio?' : '¿Activar este negocio?' }}"
                    class="btn btn-sm flex-1 justify-center sm:flex-none {{ $business->status ? 'btn-outline-secondary' : 'btn-primary' }}">
                    {{ $business->status ? 'Desactivar' : 'Activar' }}
                </button>
                @endcanany
            </div>
        </div>
    </header>

    {{-- Tabs --}}
    <div class="mb-6 flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
        <button type="button" @click="tab = 'info'"
            :class="tab === 'info' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-600 hover:text-slate-900'"
            class="rounded-lg px-4 py-2 text-sm font-medium transition">
            Información
        </button>
        <button type="button" @click="tab = 'users'"
            :class="tab === 'users' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-600 hover:text-slate-900'"
            class="rounded-lg px-4 py-2 text-sm font-medium transition">
            Usuarios
            <span class="ml-1 text-xs text-slate-400">({{ $business->users->count() }})</span>
        </button>
        <button type="button" @click="tab = 'subscriptions'"
            :class="tab === 'subscriptions' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-600 hover:text-slate-900'"
            class="rounded-lg px-4 py-2 text-sm font-medium transition">
            Suscripciones
            <span class="ml-1 text-xs text-slate-400">({{ $business->subscriptions->count() }})</span>
        </button>
    </div>

    {{-- Tab: Información --}}
    <div x-show="tab === 'info'" x-cloak class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Datos generales</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->name }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">NIT</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->nit ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Tipo de organización</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->organization_type?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Tipo de negocio</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->business_type?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Teléfono</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->phone_number ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Correo</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->email ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Ciudad</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->city?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Dirección</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->address ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Sitio web</dt>
                    <dd class="text-sm sm:col-span-2">
                        @if($business->website)
                            <a href="{{ $business->website }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">{{ $business->website }}</a>
                        @else
                            <span class="text-slate-900">—</span>
                        @endif
                    </dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Registrado</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Representante y redes</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Representante</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $rep['name'] ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Tel. representante</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $rep['phone'] ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Correo representante</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $rep['email'] ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Facebook</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->facebook ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Instagram</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->instagram ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Twitter / X</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->twitter ?? '—' }}</dd>
                </div>
            </dl>
        </section>
    </div>

    {{-- Tab: Usuarios --}}
    <div x-show="tab === 'users'" x-cloak>
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-800">Usuarios del negocio</h2>
                <p class="mt-1 text-xs text-slate-500">Personas vinculadas a este negocio en la plataforma.</p>
            </div>
            <div class="overflow-x-auto">
                @if($business->users->isEmpty())
                    <p class="p-6 text-center text-sm text-slate-400 italic">No hay usuarios asignados.</p>
                @else
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-5">Usuario</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:table-cell sm:px-5">Correo</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell md:px-5">Roles</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-5">Estado</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-5">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($business->users as $user)
                            <tr wire:key="business-user-{{ $user->id }}">
                                <td class="px-4 py-4 sm:px-5">
                                    <p class="text-sm font-medium text-slate-900">{{ $user->full_name }}</p>
                                    <p class="text-xs text-slate-400">@{{ $user->username }}</p>
                                    @if($user->pivot->is_primary || $user->id === $primary_user_id)
                                        <span class="mt-1 inline-flex rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-700">Principal</span>
                                    @endif
                                </td>
                                <td class="hidden px-4 py-4 text-sm text-slate-600 sm:table-cell sm:px-5">{{ $user->email }}</td>
                                <td class="hidden px-4 py-4 md:table-cell md:px-5">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-xs text-slate-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-4 sm:px-5">
                                    @if($user->status)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">Activo</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right sm:px-5">
                                    @canany(['users.activate', 'users.deactivate'])
                                        @if($user->id !== $primary_user_id)
                                        <button type="button" wire:click="toggleUserStatus({{ $user->id }})"
                                            wire:confirm="{{ $user->status ? '¿Desactivar este usuario?' : '¿Activar este usuario?' }}"
                                            class="text-xs font-medium {{ $user->status ? 'text-amber-700 hover:text-amber-800' : 'text-emerald-700 hover:text-emerald-800' }}">
                                            {{ $user->status ? 'Desactivar' : 'Activar' }}
                                        </button>
                                        @else
                                        <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    @endcanany
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>
    </div>

    {{-- Tab: Suscripciones --}}
    <div x-show="tab === 'subscriptions'" x-cloak>
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-800">Suscripciones</h2>
                        <p class="mt-1 text-xs text-slate-500">Historial de planes y facturación del negocio.</p>
                    </div>
                    @role('superAdmin')
                        @if($business->subscriptions->contains('status', 'pending'))
                        <a href="{{ route('admin.payments.index') }}" wire:navigate class="text-xs font-medium text-amber-700 hover:text-amber-800">
                            Ver pagos pendientes →
                        </a>
                        @endif
                    @endrole
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($business->subscriptions as $subscription)
                <div class="p-4 sm:p-5" wire:key="subscription-{{ $subscription->id }}">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-semibold text-slate-900">{{ $subscription->plan?->name ?? 'Plan' }}</h3>
                        @php
                            $status_colors = [
                                'pending'   => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                'trial'     => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                'active'    => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                'past_due'  => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
                                'cancelled' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
                                'expired'   => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                            ];
                            $color = $status_colors[$subscription->status] ?? 'bg-slate-100 text-slate-600 ring-slate-500/20';
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $color }}">
                            {{ $subscription->status_label }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Ciclo</p>
                            <p class="text-sm text-slate-900">{{ $subscription->billing_cycle_label }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Precio total</p>
                            <p class="text-sm text-slate-900">${{ number_format((float) $subscription->total_price, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Inicio</p>
                            <p class="text-sm text-slate-900">{{ $subscription->started_at?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Fin</p>
                            <p class="text-sm text-slate-900">{{ $subscription->ends_at?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                    </div>
                    @if($subscription->invoices->isNotEmpty())
                    <div class="mt-4">
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Facturas ({{ $subscription->invoices->count() }})</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($subscription->invoices->take(5) as $invoice)
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] text-slate-600" title="{{ $invoice->status ?? '' }}">
                                    {{ $invoice->invoice_number ?? 'Factura #' . $invoice->id }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @empty
                <p class="p-6 text-center text-sm text-slate-400 italic">Sin suscripciones registradas.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
