@extends('pdf.layout')

@section('content')
@php $business = $workOrder->business; @endphp
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
            <h2>ORDEN DE TRABAJO</h2>
            <p class="ref">{{ $workOrder->reference }}</p>
            <p class="muted">Estado: {{ $workOrder->status_label }}</p>
            <p class="muted">Fecha: {{ $workOrder->created_at->format('d/m/Y') }}</p>
            @if($workOrder->estimated_delivery)
            <p class="muted">Entrega est.: {{ $workOrder->estimated_delivery->format('d/m/Y') }}</p>
            @endif
            @if($workOrder->quotation)
            <p class="muted">Cotización: {{ $workOrder->quotation->reference }}</p>
            @endif
        </td>
    </tr>
</table>

<table class="grid section">
    <tr>
        <td>
            <div class="section-title">Cliente</div>
            <p class="bold">{{ $workOrder->client?->name }}</p>
            @if($workOrder->client?->document_number)
            <p>{{ $workOrder->client->document_type }} {{ $workOrder->client->document_number }}</p>
            @endif
            @if($workOrder->client?->phone)<p>{{ $workOrder->client->phone }}</p>@endif
        </td>
        <td>
            <div class="section-title">Equipo</div>
            <p>{{ $workOrder->equipment?->select_label ?? '—' }}</p>
            <p>Km entrada: {{ number_format((int) $workOrder->km_entry) }}</p>
            @if($workOrder->km_exit)<p>Km salida: {{ number_format((int) $workOrder->km_exit) }}</p>@endif
        </td>
    </tr>
</table>

@if(! empty($document_client))
<div class="box">
    <p class="bold">Documento del cliente</p>
    @foreach($document_client as $label => $value)
    <p><span class="muted">{{ $document_labels[$label] ?? $label }}:</span> {{ $value }}</p>
    @endforeach
</div>
@endif

@if($workOrder->diagnosis)
<div class="box">
    <p class="bold">Diagnóstico</p>
    <p>{{ $workOrder->diagnosis }}</p>
</div>
@endif

@if($workOrder->work_description)
<div class="box">
    <p class="bold">Descripción del trabajo</p>
    <p>{{ $workOrder->work_description }}</p>
</div>
@endif

<table class="items">
    <thead>
        <tr>
            <th>#</th>
            <th>Descripción</th>
            <th>Tipo</th>
            <th class="text-right">Cant.</th>
            <th class="text-right">P. Unit.</th>
            <th class="text-right">Desc.</th>
            <th class="text-right">Subtotal</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($workOrder->items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                {{ $item->description }}
                @if($item->technician_notes)<br><span class="muted">{{ $item->technician_notes }}</span>@endif
            </td>
            <td>{{ $item->productType?->name ?? '—' }}</td>
            <td class="text-right">{{ $item->quantity }}</td>
            <td class="text-right">{{ col_money($item->unit_price) }}</td>
            <td class="text-right">{{ $item->discount_percentage > 0 ? $item->discount_percentage.'%' : '—' }}</td>
            <td class="text-right bold">{{ col_money($item->subtotal) }}</td>
            <td>{{ $item->status_label }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td>Subtotal</td><td class="text-right">{{ col_money($workOrder->subtotal) }}</td></tr>
    <tr><td>IVA ({{ $workOrder->tax_percentage }}%)</td><td class="text-right">{{ col_money($workOrder->tax_amount) }}</td></tr>
    <tr class="total"><td>TOTAL</td><td class="text-right">{{ col_money($workOrder->total) }}</td></tr>
</table>

@if($workOrder->observations || $workOrder->notes)
<div class="section">
    @if($workOrder->observations)<p><strong>Observaciones:</strong> {{ $workOrder->observations }}</p>@endif
    @if($workOrder->notes)<p><strong>Notas:</strong> {{ $workOrder->notes }}</p>@endif
</div>
@endif

<table class="signatures">
    <tr>
        <td>
            <p class="bold">Elaborado por</p>
            <div class="sig-line">{{ $workOrder->createdBy?->full_name ?? $workOrder->createdBy?->username }}</div>
        </td>
        <td>
            <p class="bold">Recibido / Autorizado</p>
            <div class="sig-line"></div>
        </td>
    </tr>
</table>
@endsection
