<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.billing.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Facturación</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.billing.invoices.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Facturas</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $invoice_record['number'] }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Facturación</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $invoice_record['number'] }}</h1>
                    @php
                        $badge = match ($invoice_record['status']) {
                            'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                            'overdue' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                            'draft' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
                            default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        };
                    @endphp
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $invoice_record['status_label'] }}</span>
                </div>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Detalle del cobro y pagos asociados.</p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.presentation.billing.invoices.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Volver</a>
                @if($invoice_record['status'] !== 'paid')
                <x-ui.create-button wire:click="openPayment" size="sm" class="flex-1 justify-center sm:flex-none">
                    Registrar pago
                </x-ui.create-button>
                @endif
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Datos de la factura</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Estudiante</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $invoice_record['student'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Acudiente</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $invoice_record['guardian'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Concepto</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $invoice_record['concept'] }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Valor</dt>
                    <dd class="text-sm tabular-nums text-slate-900 sm:col-span-2">{{ col_money($invoice_record['amount']) }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Emisión</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ \Carbon\Carbon::parse($invoice_record['issued_at'])->format('d/m/Y') }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Vencimiento</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ \Carbon\Carbon::parse($invoice_record['due_date'])->format('d/m/Y') }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Pagos</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-3 py-3 sm:px-5">Fecha</th>
                            <th class="px-3 py-3 sm:px-5">Valor</th>
                            <th class="hidden px-3 py-3 sm:table-cell sm:px-5">Medio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $payment)
                        <tr>
                            <td class="px-3 py-4 sm:px-5">
                                <p class="text-slate-900">{{ \Carbon\Carbon::parse($payment['paid_at'])->format('d/m/Y') }}</p>
                                <p class="text-xs text-slate-500">{{ $payment['reference'] }}</p>
                            </td>
                            <td class="px-3 py-4 tabular-nums text-slate-700 sm:px-5">{{ col_money($payment['amount']) }}</td>
                            <td class="hidden px-3 py-4 text-slate-700 sm:table-cell sm:px-5">{{ $payment['method'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-3 py-8 text-center text-sm text-slate-500 sm:px-5">Aún no hay pagos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @if($showPaymentModal)
    <x-ui.modal centered maxWidth="lg">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closePayment"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">Registrar pago</h3>
            <button type="button" wire:click="closePayment" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="savePayment" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Valor <span class="text-rose-500">*</span></label>
                    <input type="number" min="1" wire:model="payment_amount"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('payment_amount') border-rose-400 bg-rose-50 @enderror">
                    @error('payment_amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Medio de pago</label>
                    <select wire:model="payment_method"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="Transferencia">Transferencia</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Referencia</label>
                    <input type="text" wire:model="payment_reference"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                </div>
            </div>
            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closePayment" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">Guardar</button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
