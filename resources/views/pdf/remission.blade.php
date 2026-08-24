@extends('pdf.layout')

@section('content')
@php $business = $remission->business; @endphp
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
            <h2>REMISIÓN</h2>
            <p class="ref">{{ $remission->reference }}</p>
            <p class="muted">Tipo: {{ $remission->type_label }}</p>
            <p class="muted">Estado: {{ $remission->status_label }}</p>
            @if($remission->issue_date)<p class="muted">Fecha: {{ $remission->issue_date->format('d/m/Y') }}</p>@endif
            @if($remission->quotation_or_po_reference)<p class="muted">Cot. / OC: {{ $remission->quotation_or_po_reference }}</p>@endif
        </td>
    </tr>
</table>

<table class="grid section">
    <tr>
        <td>
            <div class="section-title">Cliente</div>
            <p class="bold">{{ $remission->client?->name }}</p>
            @if($remission->client?->document_number)
            <p>{{ $remission->client->document_type }} {{ $remission->client->document_number }}</p>
            @endif
            @if($remission->client?->phone)<p>{{ $remission->client->phone }}</p>@endif
        </td>
        <td>
            <div class="section-title">Equipos / OT</div>
            @forelse($remission->equipments as $equipment)
                <p>{{ $equipment->select_label ?? $equipment->plate }}</p>
            @empty
                <p>—</p>
            @endforelse
            @if($remission->workOrder)<p>OT: {{ $remission->workOrder->reference }}</p>@endif
        </td>
    </tr>
</table>

<table class="grid section">
    <tr>
        <td>
            <div class="section-title">Destino</div>
            @if($remission->delivery_address)<p>{{ $remission->delivery_address }}</p>@endif
            @if($remission->delivery_city)<p>{{ $remission->delivery_city }}</p>@endif
            @if($remission->delivery_contact)<p>Contacto: {{ $remission->delivery_contact }}</p>@endif
            @if($remission->delivery_phone)<p>Tel: {{ $remission->delivery_phone }}</p>@endif
            @if($remission->delivery_observations)<p>{{ $remission->delivery_observations }}</p>@endif
        </td>
        <td>
            @if($remission->workOrder?->associatedDocuments?->isNotEmpty())
            <div class="section-title">Documentos asociados</div>
            @foreach($remission->workOrder->associatedDocuments as $document)
            <p><span class="muted">{{ $document->name }}:</span> {{ $document->value }}</p>
            @endforeach
            @endif
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
            <th class="text-right">Completados</th>
            <th class="text-right">Cancelados</th>
        </tr>
    </thead>
    <tbody>
        @forelse(($remission->workOrder?->items ?? collect()) as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->equipment?->select_label ?? $item->equipment?->plate ?? '—' }}</td>
            <td>
                {{ $item->description }}
                @if($item->technician_notes)<br><span class="muted">{{ $item->technician_notes }}</span>@endif
            </td>
            <td>{{ $item->productType?->name ?? '—' }}</td>
            <td class="text-right bold">{{ $item->quantity + 0 }}</td>
            <td class="text-right">{{ $item->quantity_complete + 0 }}</td>
            <td class="text-right">{{ $item->quantity_canceled + 0 }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="muted">Sin ítems en la OT asociada.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@php
    $wo_items = $remission->workOrder?->items ?? collect();
@endphp
@if($wo_items->isNotEmpty())
<p class="text-right bold" style="margin-top:8px;">
    Total cant.: {{ $wo_items->sum(fn ($i) => (float) $i->quantity) + 0 }}
    · Completados: {{ $wo_items->sum(fn ($i) => (float) $i->quantity_complete) + 0 }}
    · Cancelados: {{ $wo_items->sum(fn ($i) => (float) $i->quantity_canceled) + 0 }}
</p>
@endif

@if($remission->observations)
<div class="box">
    <p class="bold">Observaciones</p>
    <p>{{ $remission->observations }}</p>
</div>
@endif

<table class="signatures">
    <tr>
        <td>
            <p class="bold">Entregado por</p>
            <div class="sig-line">{{ $remission->delivered_by_name ?: '' }}</div>
            @if($remission->delivered_by_position)<p class="muted">{{ $remission->delivered_by_position }}</p>@endif
            @if($remission->delivered_by_document)<p class="muted">C.C. {{ $remission->delivered_by_document }}</p>@endif
        </td>
        <td>
            <p class="bold">Recibido por</p>
            <div class="sig-line">{{ $remission->received_by_name ?: '' }}</div>
            @if($remission->received_by_position)<p class="muted">{{ $remission->received_by_position }}</p>@endif
            @if($remission->received_by_document)<p class="muted">C.C. {{ $remission->received_by_document }}</p>@endif
        </td>
    </tr>
</table>
@endsection
