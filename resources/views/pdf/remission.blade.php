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
            <div class="section-title">Equipo / OT</div>
            <p>{{ $remission->equipment?->select_label ?? '—' }}</p>
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
            @if(! empty($document_client))
            <div class="section-title">Documento del cliente</div>
            @foreach($document_client as $label => $value)
            <p><span class="muted">{{ $document_labels[$label] ?? $label }}:</span> {{ $value }}</p>
            @endforeach
            @endif
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>#</th>
            <th>Descripción</th>
            <th>Tipo</th>
            <th>Categoría</th>
            <th>Ref./Marca</th>
            <th class="text-right">Cant.</th>
            <th>Unidad</th>
        </tr>
    </thead>
    <tbody>
        @foreach($remission->items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                {{ $item->description }}
                @if($item->observations)<br><span class="muted">{{ $item->observations }}</span>@endif
            </td>
            <td>{{ $item->productType?->name ?? '—' }}</td>
            <td>{{ $item->productCategory?->name ?? '—' }}</td>
            <td>{{ $item->reference_brand ?? '—' }}</td>
            <td class="text-right bold">{{ $item->quantity }}</td>
            <td>{{ $item->unit_name ?? $item->unit?->name ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p class="text-right bold" style="margin-top:8px;">Total ítems: {{ $remission->total_items }}</p>

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
