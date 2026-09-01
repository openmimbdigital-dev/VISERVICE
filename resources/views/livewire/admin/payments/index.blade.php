<div class="p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pagos Pendientes</h1>
            <p class="text-sm text-slate-500 mt-1">Revisa y confirma los pagos de nuevos comercios registrados.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $stats['pending'] }}</p>
                <p class="text-sm text-slate-500">Pendientes de revisión</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $stats['confirmed_today'] }}</p>
                <p class="text-sm text-slate-500">Confirmados hoy</p>
            </div>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por nombre del comercio..."
                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
        </div>
    </div>

    {{-- Tabla de pagos --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        @if($invoices->isEmpty())
        <div class="py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="mt-3 text-sm font-medium text-slate-900">No hay pagos pendientes</p>
            <p class="mt-1 text-xs text-slate-500">Todos los pagos han sido procesados.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Comercio</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Pago</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($invoices as $invoice)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $invoice->business->name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">NIT: {{ $invoice->business->nit }}</p>
                                <p class="text-xs text-slate-400">{{ $invoice->invoice_number }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $invoice->subscription->plan->name }}</p>
                                <p class="text-xs text-slate-500">{{ $invoice->subscription->billing_cycle_label }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1.5">
                                @if($invoice->payment_method === 'transfer')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                        Transferencia
                                    </span>
                                    @if($invoice->payment_proof)
                                        <a href="{{ $this->getProofUrl($invoice->payment_proof) }}" target="_blank"
                                            class="flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 010 18z"/></svg>
                                            Ver comprobante
                                        </a>
                                    @else
                                        <p class="text-xs text-slate-400 italic">Sin comprobante</p>
                                    @endif
                                    @if($invoice->payment_reference)
                                        <p class="text-xs text-slate-500">Ref: {{ $invoice->payment_reference }}</p>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Efectivo
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-slate-900">${{ number_format($invoice->amount, 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-500">COP</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-slate-700">{{ $invoice->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-400">{{ $invoice->created_at->format('H:i') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openConfirm({{ $invoice->id }})" type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Confirmar
                                </button>
                                <button wire:click="rejectPayment({{ $invoice->id }})"
                                    wire:confirm="¿Seguro que deseas rechazar este pago? La suscripción quedará cancelada."
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Rechazar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="border-t border-slate-100 px-6 py-4">
            {{ $invoices->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Modal Confirmar Pago --}}
    @if($showConfirmModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        x-data x-on:keydown.escape.window="$wire.closeModal()">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Confirmar pago</h3>
                <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="flex items-start gap-3 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-emerald-800">Al confirmar, la factura pasará a <strong>pagada</strong> y la suscripción se activará inmediatamente.</p>
                </div>

                {{-- Cuenta destino (solo si es transferencia) --}}
                @if($confirm_payment_method === 'transfer')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Cuenta donde se recibió el pago
                        <span class="text-slate-400 font-normal text-xs ml-1">(opcional)</span>
                    </label>
                    <select wire:model="bank_account_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                        <option value="">— Sin especificar —</option>
                        @foreach($bankAccounts as $ba)
                            <option value="{{ $ba->id }}">
                                {{ $ba->bank?->name ?? 'Banco' }} · {{ $ba->account_type_label }} · {{ $ba->account_number }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Selecciona la cuenta para llevar el registro en el panel financiero.</p>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Notas internas (opcional)</label>
                    <textarea wire:model="admin_notes" rows="3" placeholder="Ej: Transferencia verificada vía app bancaria..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition resize-none"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                <button wire:click="closeModal" type="button"
                    class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </button>
                <button wire:click="confirmPayment" type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Confirmar pago
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
