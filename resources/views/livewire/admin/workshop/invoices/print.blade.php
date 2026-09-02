@php
    $business = $invoice->business;
@endphp
<div class="mx-auto max-w-4xl p-6 text-sm text-slate-800">
    <header class="border-b-2 border-slate-800 pb-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                @if($business?->logo_url)
                <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" class="mb-2 h-14 object-contain">
                @endif
                <h1 class="text-xl font-bold uppercase">{{ $business?->name }}</h1>
                @if($business?->tagline)
                <p class="text-xs italic text-slate-600">{{ $business->tagline }}</p>
                @endif
                @if($business?->tax_regime)
                <p class="text-xs text-slate-500">{{ $business->tax_regime }}</p>
                @endif
                <p class="mt-1 text-xs">NIT: {{ $business?->nit }}</p>
                @if($business?->address)<p class="text-xs">{{ $business->address }}</p>@endif
                @if($business?->phone_number)<p class="text-xs">Tel: {{ $business->phone_number }}</p>@endif
                @if($business?->email)<p class="text-xs">{{ $business->email }}</p>@endif
            </div>
            <div class="text-right">
                <p class="text-lg font-bold text-indigo-700">FACTURA</p>
                <p class="font-mono text-base font-semibold">{{ $invoice->reference }}</p>
                <p class="text-xs text-slate-500">Estado: {{ $invoice->status_label }}</p>
                <p class="text-xs text-slate-500">Fecha: {{ $invoice->created_at->format('d/m/Y') }}</p>
                @if($invoice->due_date)
                <p class="text-xs text-slate-500">Vence: {{ $invoice->due_date->format('d/m/Y') }}</p>
                @endif
                @if($invoice->workOrder)
                <p class="text-xs">OT: {{ $invoice->workOrder->reference }}</p>
                @endif
            </div>
        </div>
    </header>

    <section class="mt-4 grid grid-cols-2 gap-4 text-xs">
        <div>
            <p class="font-semibold uppercase text-slate-500">Cliente</p>
            <p class="font-medium">{{ $invoice->workOrder?->client?->name ?? '—' }}</p>
            @if($invoice->workOrder?->client?->document_number)
            <p>{{ $invoice->workOrder->client->document_type }} {{ $invoice->workOrder->client->document_number }}</p>
            @endif
            @if($invoice->workOrder?->client?->phone)
            <p>{{ $invoice->workOrder->client->phone }}</p>
            @endif
        </div>
        <div>
            <p class="font-semibold uppercase text-slate-500">Información de pago</p>
            @if($invoice->paid_at)
            <p>Pagada: {{ $invoice->paid_at->format('d/m/Y H:i') }}</p>
            @endif
            @if($invoice->payment_method)
            <p>Método: {{ $invoice->payment_method }}</p>
            @endif
            @if($invoice->payment_reference)
            <p>Referencia: {{ $invoice->payment_reference }}</p>
            @endif
        </div>
    </section>

    <table class="mt-6 w-full border-collapse text-xs">
        <thead>
            <tr class="border-b-2 border-slate-300 bg-slate-50">
                <th class="px-2 py-2 text-left">#</th>
                <th class="px-2 py-2 text-left">Equipo</th>
                <th class="px-2 py-2 text-left">Descripción</th>
                <th class="px-2 py-2 text-left">Tipo</th>
                <th class="px-2 py-2 text-right">Cant.</th>
                <th class="px-2 py-2 text-right">V. unit.</th>
                <th class="px-2 py-2 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $index => $invoiceItem)
            @php $item = $invoiceItem->workOrderItem; @endphp
            <tr class="border-b border-slate-100">
                <td class="px-2 py-2">{{ $index + 1 }}</td>
                <td class="px-2 py-2">{{ $item?->equipment?->select_label ?? $item?->equipment?->plate ?? '—' }}</td>
                <td class="px-2 py-2">
                    {{ $item?->description ?? '—' }}
                    @if($item?->technician_notes)<br><span class="text-slate-500">{{ $item->technician_notes }}</span>@endif
                </td>
                <td class="px-2 py-2">{{ $item?->productType?->name ?? '—' }}</td>
                <td class="px-2 py-2 text-right font-medium">{{ $invoiceItem->quantity + 0 }}</td>
                <td class="px-2 py-2 text-right">{{ $item ? col_money($item->unit_price) : '—' }}</td>
                <td class="px-2 py-2 text-right font-medium">{{ $item ? col_money($item->subtotal) : '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-2 py-4 text-center text-slate-500">Sin ítems.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-6 ml-auto w-64 text-xs">
        <div class="flex justify-between border-b border-slate-100 py-1">
            <span class="text-slate-500">Subtotal</span>
            <span class="font-medium">{{ col_money($invoice->subtotal) }}</span>
        </div>
        <div class="flex justify-between border-b border-slate-100 py-1">
            <span class="text-slate-500">Impuesto ({{ rtrim(rtrim(number_format((float) $invoice->tax_percentage, 2, '.', ''), '0'), '.') }}%)</span>
            <span class="font-medium">{{ col_money($invoice->tax_amount) }}</span>
        </div>
        <div class="flex justify-between py-2 text-sm font-bold text-indigo-700">
            <span>Total</span>
            <span>{{ col_money($invoice->total) }}</span>
        </div>
    </div>

    @if($invoice->notes)
    <section class="mt-6 rounded border border-slate-200 p-3 text-xs">
        <p class="font-semibold uppercase text-slate-500">Notas</p>
        <p class="mt-1">{{ $invoice->notes }}</p>
    </section>
    @endif
</div>
