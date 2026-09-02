@extends('pdf.layout')

@section('content')
@php $business = $invoice->business; @endphp
<table class="header">
    <tr>
        <td>
            <h1>{{ $business?->name }}</h1>
            @if($business?->tagline)<p class="muted"><em>{{ $business->tagline }}</em></p>@endif
            @if($business?->tax_regime)<p class="muted">{{ $business->tax_regime }}</p>@endif
            <p class="muted">NIT: {{ $business?->nit }}</p>
            @if($business?->address)<p class="muted">{{ $business->address }}</p>@endif
            @if($business?->phone_number)<p class="muted">Tel: {{ $business->phone_number }}</p>@endif
            @if($business?->email)<p class="muted">{{ $business->email }}</p>@endif
        </td>
        <td class="header-right">
            <h2>FACTURA</h2>
            <p class="ref">{{ $invoice->reference }}</p>
            <p class="muted">Estado: {{ $invoice->status_label }}</p>
            <p class="muted">Fecha: {{ $invoice->created_at->format('d/m/Y') }}</p>
            @if($invoice->due_date)<p class="muted">Vence: {{ $invoice->due_date->format('d/m/Y') }}</p>@endif
            @if($invoice->workOrder)<p class="muted">OT: {{ $invoice->workOrder->reference }}</p>@endif
        </td>
    </tr>
</table>

<table class="grid section">
    <tr>
        <td>
            <div class="section-title">Cliente</div>
            <p class="bold">{{ $invoice->workOrder?->client?->name ?? '—' }}</p>
            @if($invoice->workOrder?->client?->document_number)
            <p>{{ $invoice->workOrder->client->document_type }} {{ $invoice->workOrder->client->document_number }}</p>
            @endif
            @if($invoice->workOrder?->client?->phone)<p>{{ $invoice->workOrder->client->phone }}</p>@endif
        </td>
        <td>
            <div class="section-title">Información de pago</div>
            @if($invoice->paid_at)<p>Pagada: {{ $invoice->paid_at->format('d/m/Y H:i') }}</p>@endif
            @if($invoice->payment_method)<p>Método: {{ $invoice->payment_method }}</p>@endif
            @if($invoice->payment_reference)<p>Referencia: {{ $invoice->payment_reference }}</p>@endif
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>#</th>
            <th>Equipo</th>
            <th>Descripción</th>
            <th>Tipo</th>
            <th class="text-right">Cant.</th>
            <th class="text-right">Complet.</th>
            <th class="text-right">Cancel.</th>
            <th class="text-right">V. unit.</th>
            <th class="text-right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoice->items as $index => $invoiceItem)
        @php $item = $invoiceItem->workOrderItem; @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item?->equipment?->select_label ?? $item?->equipment?->plate ?? '—' }}</td>
            <td>
                {{ $item?->description ?? '—' }}
                @if($item?->technician_notes)<br><span class="muted">{{ $item->technician_notes }}</span>@endif
            </td>
            <td>{{ $item?->productType?->name ?? '—' }}</td>
            <td class="text-right bold">{{ $invoiceItem->quantity + 0 }}</td>
            <td class="text-right">{{ $invoiceItem->quantity_complete + 0 }}</td>
            <td class="text-right">{{ $invoiceItem->quantity_canceled + 0 }}</td>
            <td class="text-right">{{ $item ? col_money($item->unit_price) : '—' }}</td>
            <td class="text-right bold">{{ $item ? col_money($item->subtotal) : '—' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="muted">Sin ítems en esta factura.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<table class="totals">
    <tr>
        <td class="muted">Subtotal</td>
        <td class="text-right bold">{{ col_money($invoice->subtotal) }}</td>
    </tr>
    <tr>
        <td class="muted">Impuesto ({{ rtrim(rtrim(number_format((float) $invoice->tax_percentage, 2, '.', ''), '0'), '.') }}%)</td>
        <td class="text-right bold">{{ col_money($invoice->tax_amount) }}</td>
    </tr>
    <tr class="total">
        <td>Total</td>
        <td class="text-right">{{ col_money($invoice->total) }}</td>
    </tr>
</table>

@if($invoice->notes)
<div class="box">
    <div class="section-title">Notas</div>
    <p>{{ $invoice->notes }}</p>
</div>
@endif
@endsection
