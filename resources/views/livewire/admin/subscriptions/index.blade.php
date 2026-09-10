<div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Suscripciones</h2>
                <p class="text-sm text-gray-500 mt-1">Gestiona las suscripciones de cada comercio</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.subscriptions.plans.index') }}" class="btn btn-secondary">
                    Ver Planes
                </a>
                <button wire:click="openCreate" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva Suscripción
                </button>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-3xl font-bold text-green-600">{{ $stats['active'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Activas</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['trial'] }}</p>
                <p class="text-sm text-gray-500 mt-1">En prueba</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-3xl font-bold text-yellow-600">{{ $stats['expired'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Vencidas</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-3xl font-bold text-gray-500">{{ $stats['cancelled'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Canceladas</p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por comercio..." class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <select wire:model.live="filter_status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todos los estados</option>
                    <option value="trial">En prueba</option>
                    <option value="active">Activa</option>
                    <option value="past_due">Vencida (pago)</option>
                    <option value="cancelled">Cancelada</option>
                    <option value="expired">Expirada</option>
                </select>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Comercio</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ciclo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Precio</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vigencia</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($subscriptions as $sub)
                            @php
                                $colors = [
                                    'green'  => 'bg-green-100 text-green-700',
                                    'blue'   => 'bg-blue-100 text-blue-700',
                                    'yellow' => 'bg-yellow-100 text-yellow-700',
                                    'gray'   => 'bg-gray-100 text-gray-600',
                                    'red'    => 'bg-red-100 text-red-700',
                                ];
                                $color = $colors[$sub->status_color] ?? $colors['gray'];
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $sub->business->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $sub->business->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $sub->plan->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $sub->billing_cycle_label }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    ${{ number_format($sub->total_price, 0, ',', '.') }}
                                    @if($sub->discount_percentage > 0)
                                        <span class="text-xs text-green-600">({{ $sub->discount_percentage }}%)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    <p>{{ $sub->started_at->format('d/m/Y') }}</p>
                                    <p class="{{ $sub->isExpired() ? 'text-red-500 font-medium' : '' }}">→ {{ $sub->ends_at->format('d/m/Y') }}</p>
                                    @if($sub->isActive() && !$sub->isExpired())
                                        <p class="text-gray-400">{{ $sub->daysUntilExpiry() }} días restantes</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                        {{ $sub->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="viewPayments({{ $sub->id }})" type="button"
                                            class="btn btn-sm btn-outline-secondary" title="Ver detalle de los pagos">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                        </button>
                                        @if(in_array($sub->status, ['pending', 'active', 'trial', 'past_due']))
                                            <button wire:click="generatePaymentLink({{ $sub->id }})"
                                                wire:loading.attr="disabled" wire:target="generatePaymentLink({{ $sub->id }})"
                                                class="btn btn-sm btn-outline-primary" title="Link de pago en línea">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5m5.656-5.656l1.5-1.5a4 4 0 015.656 5.656l-3 3a4 4 0 01-5.656 0"/>
                                                </svg>
                                            </button>
                                        @endif
                                        @if(in_array($sub->status, ['active', 'trial', 'past_due']))
                                            <button wire:click="openInvoiceModal({{ $sub->id }})" class="btn btn-sm btn-success" title="Registrar pago manual">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        @endif
                                        <button wire:click="openEdit({{ $sub->id }})" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        @if(in_array($sub->status, ['active', 'trial']))
                                            <button wire:click="cancel({{ $sub->id }})"
                                                class="btn btn-sm btn-danger" title="Cancelar">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p>No hay suscripciones registradas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $subscriptions->links() }}
            </div>
        </div>

    {{-- Modal crear/editar suscripción --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $selected_subscription_id ? 'Editar Suscripción' : 'Nueva Suscripción' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Comercio *</label>
                        <select wire:model="business_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Seleccionar comercio...</option>
                            @foreach($businesses as $biz)
                                <option value="{{ $biz->id }}">{{ $biz->name }}</option>
                            @endforeach
                        </select>
                        @error('business_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plan *</label>
                        <select wire:model="subscription_plan_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Seleccionar plan...</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} — ${{ number_format($plan->monthly_price, 0, ',', '.') }}/mes</option>
                            @endforeach
                        </select>
                        @error('subscription_plan_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ciclo de facturación *</label>
                            <select wire:model="billing_cycle" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="monthly">Mensual</option>
                                <option value="quarterly">Trimestral</option>
                                <option value="semiannual">Semestral</option>
                                <option value="annual">Anual</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio *</label>
                            <input type="date" wire:model="started_at" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('started_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-3 bg-blue-50 rounded-lg">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="is_trial" class="custom-checkbox">
                            <span class="text-sm font-medium text-blue-700">Período de prueba</span>
                        </label>
                        @if($is_trial)
                            <div class="flex items-center gap-2">
                                <input type="number" wire:model="trial_days" min="1" max="90" class="w-20 border border-blue-300 rounded-lg px-2 py-1 text-sm text-center">
                                <span class="text-sm text-blue-600">días</span>
                            </div>
                        @endif
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="auto_renew" class="custom-checkbox">
                        <span class="text-sm font-medium text-gray-700">Renovación automática</span>
                    </label>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                        <textarea wire:model="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Observaciones opcionales..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $selected_subscription_id ? 'Actualizar' : 'Crear Suscripción' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal registrar pago --}}
    @if($showInvoiceModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Registrar Pago</h3>
                    <button wire:click="closeInvoiceModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="registerPayment" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Método de pago *</label>
                        <select wire:model="payment_method" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Seleccionar...</option>
                            <option value="transferencia">Transferencia bancaria</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta de crédito/débito</option>
                            <option value="pse">PSE</option>
                            <option value="nequi">Nequi</option>
                            <option value="daviplata">Daviplata</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('payment_method') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Referencia de pago</label>
                        <input type="text" wire:model="payment_reference" placeholder="Número de transacción, comprobante..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de pago *</label>
                        <input type="date" wire:model="paid_at" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('paid_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                        <textarea wire:model="invoice_notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <button type="button" wire:click="closeInvoiceModal" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-success">Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    
    {{-- Link de pago en línea --}}
    @if($showPaymentLinkModal)
    <x-ui.modal centered maxWidth="md">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closePaymentLinkModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Link de pago en línea</h3>
                <p class="mt-0.5 text-xs text-slate-500">Cobro {{ $payment_link_invoice }}</p>
            </div>
            <button type="button" wire:click="closePaymentLinkModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6" x-data="{ copied: false }">
            <p class="text-sm text-slate-600">
                Compártele este link al comercio. Puede pagar con tarjeta, PSE, Nequi o botón
                Bancolombia; cuando el pago se apruebe, la suscripción se activa y la factura
                se genera sola.
            </p>

            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                <input type="text" readonly value="{{ $payment_link_url }}" x-ref="link"
                    class="min-w-0 flex-1 border-0 bg-transparent p-0 font-mono text-xs text-slate-700 focus:outline-none focus:ring-0">
                <button type="button"
                    x-on:click="navigator.clipboard.writeText($refs.link.value); copied = true; setTimeout(() => copied = false, 2000)"
                    class="shrink-0 rounded-lg bg-white px-2.5 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-slate-200 transition hover:bg-indigo-50">
                    <span x-show="! copied">Copiar</span>
                    <span x-show="copied" x-cloak class="text-emerald-700">¡Copiado!</span>
                </button>
            </div>

            <a href="{{ $payment_link_url }}" target="_blank" rel="noopener"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Abrir el checkout
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>

            <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-500">
                El link se reutiliza mientras siga vigente: volver a pulsar el botón no crea
                uno nuevo. El cobro con transferencia y comprobante sigue disponible.
            </p>
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-100 px-4 py-4 sm:px-6">
            <button type="button" wire:click="closePaymentLinkModal" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200">Cerrar</button>
        </div>
    </x-ui.modal>
    @endif

    {{-- Detalle de los pagos --}}
    @if($showPaymentsModal)
    <x-ui.modal centered maxWidth="2xl">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closePaymentsModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Pagos de la suscripción</h3>
                <p class="mt-0.5 text-xs text-slate-500">Cómo pagó el comercio, con el detalle de la pasarela.</p>
            </div>
            <button type="button" wire:click="closePaymentsModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 space-y-3 overflow-y-auto px-4 py-5 sm:px-6">
            @forelse($payments as $payment)
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-semibold text-slate-900">{{ $payment->methodLabel() }}</p>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $payment->isOnline() ? 'bg-violet-50 text-violet-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $payment->channelLabel() }}
                            </span>
                            @if($payment->isBackfilled())
                            <span class="text-[10px] text-slate-300" title="Registro reconstruido: de este pago solo se conservaba el método y la fecha">reconstruido</span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $payment->paid_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                            @if($payment->createdBy) · registrado por {{ $payment->createdBy->username }} @endif
                        </p>
                    </div>
                    <p class="text-lg font-bold text-emerald-700">{{ col_money($payment->amount) }}</p>
                </div>

                <dl class="mt-3 grid grid-cols-1 gap-x-6 gap-y-2 border-t border-slate-100 pt-3 text-xs sm:grid-cols-2">
                    @foreach([
                        ['Pago en la pasarela', $payment->gateway_payment_id, true],
                        ['Código de aprobación', $payment->gateway_code, true],
                        ['Referencia enviada', $payment->gateway_reference, true],
                        ['Origen', $payment->gateway_source, true],
                        ['Correo del pagador', $payment->payer_email, false],
                        ['Referencia del cliente', $payment->payment_reference, false],
                    ] as [$label, $value, $mono])
                        @if(filled($value))
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="text-right text-slate-800 {{ $mono ? 'font-mono' : '' }}">{{ $value }}</dd>
                        </div>
                        @endif
                    @endforeach

                    @if((float) $payment->tip > 0)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Propina</dt>
                        <dd class="text-slate-800">{{ col_money($payment->tip) }}</dd>
                    </div>
                    @endif
                </dl>

                @if($payment->taxes)
                <div class="mt-2 border-t border-slate-100 pt-2">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Impuestos</p>
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        @foreach($payment->taxes as $tax)
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-700">
                            {{ $tax['type'] ?? 'Impuesto' }}: {{ col_money($tax['value'] ?? 0) }}
                            <span class="text-slate-400">sobre {{ col_money($tax['base'] ?? 0) }}</span>
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($payment->notes)
                <p class="mt-2 rounded-lg border-l-2 border-slate-300 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-600">{{ $payment->notes }}</p>
                @endif
            </div>
            @empty
            <div class="py-8 text-center">
                <svg class="mx-auto h-9 w-9 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 9v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="mt-2 text-sm text-slate-400">Esta suscripción todavía no registra pagos.</p>
            </div>
            @endforelse
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-100 px-4 py-4 sm:px-6">
            <button type="button" wire:click="closePaymentsModal" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200">Cerrar</button>
        </div>
    </x-ui.modal>
    @endif
</div>
