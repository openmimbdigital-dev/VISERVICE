@extends('pdf.layout')

@section('content')
@php
    $business = $quotation->business;
    $subtotals = $category_subtotals;
@endphp
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
            <h2>COTIZACIÓN</h2>
            <p class="ref">{{ $quotation->reference }}</p>
            <p class="muted">Fecha: {{ ($quotation->issued_at ?? $quotation->created_at)->format('d/m/Y') }}</p>
            @if($quotation->valid_until)
            <p class="muted">Vigencia: {{ $quotation->valid_until->format('d/m/Y') }} ({{ $quotation->validity_days }} días)</p>
            @endif
        </td>
    </tr>
</table>

<table class="grid section">
    <tr>
        <td>
            <div class="section-title">Cliente</div>
            <p class="bold">{{ $quotation->client?->name }}</p>
            <p>{{ $quotation->client?->document_number }}</p>
            <p>{{ $quotation->client?->phone }}</p>
        </td>
        <td>
            <div class="section-title">Equipos</div>
            @forelse($quotation->equipments as $equipment)
                <p>{{ $equipment->select_label }}</p>
            @empty
                <p>—</p>
            @endforelse
            @if($quotation->hours_entry_formatted)<p>Horas al ingreso: {{ $quotation->hours_entry_formatted }}</p>@endif
            @if($quotation->quotationServiceType)<p>Tipo servicio: {{ $quotation->quotationServiceType->name }}</p>@endif
        </td>
    </tr>
</table>

@if($quotation->diagnosis)
<div class="box">
    <p class="bold">Diagnóstico</p>
    <p>{{ $quotation->diagnosis }}</p>
</div>
@endif

<table class="items">
    <thead>
        <tr>
            <th>Equipo</th>
            <th>Tipo</th>
            <th>Categoría</th>
            <th>Descripción</th>
            <th class="text-right">Cant.</th>
            <th class="text-right">P. Unit.</th>
            <th class="text-right">Desc.</th>
            <th class="text-right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($quotation->items as $item)
        <tr>
            <td>{{ $item->equipment?->select_label ?? '—' }}</td>
            <td>{{ $item->productType?->name ?? '—' }}</td>
            <td>{{ $item->productCategory?->name ?? '—' }}</td>
            <td>{{ $item->description }}</td>
            <td class="text-right">{{ $item->quantity }}</td>
            <td class="text-right">{{ col_money($item->unit_price) }}</td>
            <td class="text-right">{{ $item->discount_percentage > 0 ? $item->discount_percentage.'%' : '—' }}</td>
            <td class="text-right bold">{{ col_money($item->subtotal) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td>Subtotal Mano de Obra</td><td class="text-right">{{ col_money($subtotals['mano_obra']) }}</td></tr>
    <tr><td>Subtotal Repuestos</td><td class="text-right">{{ col_money($subtotals['repuestos']) }}</td></tr>
    <tr><td>Subtotal Lubricantes</td><td class="text-right">{{ col_money($subtotals['lubricantes']) }}</td></tr>
    <tr><td>Subtotal Otros materiales</td><td class="text-right">{{ col_money($subtotals['otros']) }}</td></tr>
    <tr><td>Subtotal</td><td class="text-right">{{ col_money($quotation->subtotal) }}</td></tr>
    <tr><td>{{ $quotation->custom_tax_name ?? 'Impuesto' }} ({{ $quotation->tax_percentage }}%)</td><td class="text-right">{{ col_money($quotation->tax_amount) }}</td></tr>
    <tr class="total"><td>TOTAL</td><td class="text-right">{{ col_money($quotation->total) }}</td></tr>
</table>

@if($quotation->execution_time || $quotation->paymentMethod || $quotation->observations)
<div class="section">
    @if($quotation->execution_time)<p><strong>Tiempo de ejecución:</strong> {{ $quotation->execution_time }}</p>@endif
    @if($quotation->paymentMethod)<p><strong>Forma de pago:</strong> {{ $quotation->paymentMethod->name }}</p>@endif
    @if($quotation->observations)<p><strong>Observaciones:</strong> {{ $quotation->observations }}</p>@endif
</div>
@endif

@if($quotation->bankAccount)
<div class="box">
    <p class="bold">Datos para consignación</p>
    <p>{{ $quotation->bankAccount->bank_name }} — {{ $quotation->bankAccount->accountTypeLabel() }}</p>
    <p>Cuenta: {{ $quotation->bankAccount->account_number }}</p>
    <p>Titular: {{ $quotation->bankAccount->account_holder }} ({{ $quotation->bankAccount->document_type }} {{ $quotation->bankAccount->document_number }})</p>
</div>
@endif

<table class="signatures">
    <tr>
        <td>
            <p class="bold">Cotizado por</p>
            <div class="sig-line">{{ $quotation->createdBy?->full_name ?? $quotation->createdBy?->username }}</div>
        </td>
        <td>
            @if($quotation->approved_by_name)
            <p class="bold">Aprobado por el cliente</p>
            <div class="sig-line">{{ $quotation->approved_by_name }}</div>
            @if($quotation->approved_by_position)<p class="muted">{{ $quotation->approved_by_position }}</p>@endif
            @endif
        </td>
    </tr>
</table>
@endsection
