<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.invoices.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Facturación</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $invoice->reference }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-mono text-2xl font-bold text-slate-900">{{ $invoice->reference }}</h1>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $invoice->status_badge_class }}">
                        {{ $invoice->status_label }}
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-600">
                    <span class="font-medium text-slate-800">{{ $invoice->workOrder?->client?->name ?? '—' }}</span>
                    @if($invoice->workOrder)
                    <a href="{{ route('admin.workshop.work-orders.show', $invoice->work_order_id) }}" wire:navigate class="text-indigo-600 hover:underline">
                        OT {{ $invoice->workOrder->reference }}
                    </a>
                    @endif
                    @if($invoice->due_date)
                    <span>Vence: {{ $invoice->due_date->format('d/m/Y') }}</span>
                    @endif
                    <span>Emitida: {{ $invoice->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.workshop.invoices.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Volver</a>
                <a href="{{ route('admin.workshop.invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Imprimir / PDF</a>
            </div>
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Datos de la factura</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                @foreach([
                    ['Referencia', $invoice->reference],
                    ['Estado', $invoice->status_label],
                    ['Vencimiento', $invoice->due_date?->format('d/m/Y') ?? '—'],
                    ['Creada por', $invoice->createdBy?->name ?? '—'],
                    ['Fecha de pago', $invoice->paid_at?->format('d/m/Y H:i') ?? '—'],
                    ['Método de pago', $invoice->payment_method ?? '—'],
                    ['Ref. de pago', $invoice->payment_reference ?? '—'],
                ] as [$label, $value])
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">{{ $label }}</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $value }}</dd>
                </div>
                @endforeach
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Totales</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Subtotal</dt>
                    <dd class="text-sm font-medium text-slate-900 sm:col-span-2">{{ col_money($invoice->subtotal) }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Impuesto ({{ rtrim(rtrim(number_format((float) $invoice->tax_percentage, 2, '.', ''), '0'), '.') }}%)</dt>
                    <dd class="text-sm font-medium text-slate-900 sm:col-span-2">{{ col_money($invoice->tax_amount) }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Total</dt>
                    <dd class="text-lg font-bold text-indigo-700 sm:col-span-2">{{ col_money($invoice->total) }}</dd>
                </div>
            </dl>
        </section>
    </div>

    @if($invoice->notes)
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Notas</h2>
        </div>
        <p class="px-5 py-4 text-sm text-slate-700">{{ $invoice->notes }}</p>
    </section>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Ítems facturados</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-5">#</th>
                        <th class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:table-cell sm:px-5">Equipo</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-5">Descripción</th>
                        <th class="hidden px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell md:px-5">Tipo</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-5">Cant.</th>
                        <th class="hidden px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 sm:table-cell sm:px-5">Complet.</th>
                        <th class="hidden px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 lg:table-cell lg:px-5">Cancel.</th>
                        <th class="hidden px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell md:px-5">V. unit.</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-5">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoice->items as $index => $invoiceItem)
                    @php $item = $invoiceItem->workOrderItem; @endphp
                    <tr>
                        <td class="whitespace-nowrap px-3 py-4 text-slate-500 sm:px-5">{{ $index + 1 }}</td>
                        <td class="hidden whitespace-nowrap px-3 py-4 text-slate-700 sm:table-cell sm:px-5">
                            {{ $item?->equipment?->select_label ?? $item?->equipment?->plate ?? '—' }}
                        </td>
                        <td class="px-3 py-4 text-slate-900 sm:px-5">
                            {{ $item?->description ?? '—' }}
                            @if($item?->technician_notes)
                            <p class="mt-0.5 text-xs text-slate-500">{{ $item->technician_notes }}</p>
                            @endif
                        </td>
                        <td class="hidden whitespace-nowrap px-3 py-4 text-slate-700 md:table-cell md:px-5">{{ $item?->productType?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-right tabular-nums text-slate-900 sm:px-5">{{ $invoiceItem->quantity + 0 }}</td>
                        <td class="hidden whitespace-nowrap px-3 py-4 text-right tabular-nums text-slate-700 sm:table-cell sm:px-5">{{ $invoiceItem->quantity_complete + 0 }}</td>
                        <td class="hidden whitespace-nowrap px-3 py-4 text-right tabular-nums text-slate-700 lg:table-cell lg:px-5">{{ $invoiceItem->quantity_canceled + 0 }}</td>
                        <td class="hidden whitespace-nowrap px-3 py-4 text-right tabular-nums text-slate-700 md:table-cell md:px-5">{{ $item ? col_money($item->unit_price) : '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-right font-medium tabular-nums text-slate-900 sm:px-5">{{ $item ? col_money($item->subtotal) : '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-8 text-center text-sm text-slate-500">Sin ítems en esta factura.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
