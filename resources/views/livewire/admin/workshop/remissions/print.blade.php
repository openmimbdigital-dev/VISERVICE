@php
    $business = $remission->business;
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
                <p class="text-lg font-bold text-indigo-700">REMISIÓN</p>
                <p class="font-mono text-base font-semibold">{{ $remission->reference }}</p>
                <p class="text-xs text-slate-500">Tipo: {{ $remission->type_label }}</p>
                <p class="text-xs text-slate-500">Estado: {{ $remission->status_label }}</p>
                @if($remission->issue_date)
                <p class="text-xs text-slate-500">Fecha: {{ $remission->issue_date->format('d/m/Y') }}</p>
                @endif
                @if($remission->quotation_or_po_reference)
                <p class="text-xs">Cot. / OC: {{ $remission->quotation_or_po_reference }}</p>
                @endif
            </div>
        </div>
    </header>

    <section class="mt-4 grid grid-cols-2 gap-4 text-xs">
        <div>
            <p class="font-semibold uppercase text-slate-500">Cliente</p>
            <p class="font-medium">{{ $remission->client?->name }}</p>
            @if($remission->client?->document_number)
            <p>{{ $remission->client->document_type }} {{ $remission->client->document_number }}</p>
            @endif
            @if($remission->client?->phone)
            <p>{{ $remission->client->phone }}</p>
            @endif
        </div>
        <div>
            <p class="font-semibold uppercase text-slate-500">Equipos / OT</p>
            @forelse($remission->equipments as $equipment)
                <p>{{ $equipment->select_label ?? $equipment->plate }}</p>
            @empty
                <p>—</p>
            @endforelse
            @if($remission->workOrder)
            <p>OT: {{ $remission->workOrder->reference }}</p>
            @endif
        </div>
    </section>

    <section class="mt-4 grid grid-cols-2 gap-4 text-xs">
        <div>
            <p class="font-semibold uppercase text-slate-500">Destino</p>
            @if($remission->delivery_address)<p>{{ $remission->delivery_address }}</p>@endif
            @if($remission->delivery_city)<p>{{ $remission->delivery_city }}</p>@endif
            @if($remission->delivery_contact)<p>Contacto: {{ $remission->delivery_contact }}</p>@endif
            @if($remission->delivery_phone)<p>Tel: {{ $remission->delivery_phone }}</p>@endif
            @if($remission->delivery_observations)<p class="mt-1">{{ $remission->delivery_observations }}</p>@endif
        </div>
        @if($remission->workOrder?->associatedDocuments?->isNotEmpty())
        <div>
            <p class="font-semibold uppercase text-slate-500">Documentos asociados</p>
            @foreach($remission->workOrder->associatedDocuments as $document)
            <p><span class="text-slate-500">{{ $document->name }}:</span> {{ $document->value }}</p>
            @endforeach
        </div>
        @endif
    </section>

    @include('livewire.admin.workshop.remissions.partials.work-order-items', [
        'items' => $remission->workOrder?->items ?? collect(),
        'variant' => 'compact',
    ])

    @if($remission->observations)
    <section class="mt-4 rounded border border-slate-200 p-2 text-xs">
        <p class="font-semibold">Observaciones</p>
        <p>{{ $remission->observations }}</p>
    </section>
    @endif

    <footer class="mt-10 grid grid-cols-2 gap-8 text-xs">
        <div>
            <p class="font-semibold">Entregado por</p>
            <p class="mt-6 border-t border-slate-400 pt-1">{{ $remission->delivered_by_name ?: '________________' }}</p>
            @if($remission->delivered_by_position)
            <p class="text-slate-500">{{ $remission->delivered_by_position }}</p>
            @endif
            @if($remission->delivered_by_document)
            <p class="text-slate-500">C.C. {{ $remission->delivered_by_document }}</p>
            @endif
        </div>
        <div>
            <p class="font-semibold">Recibido por</p>
            <p class="mt-6 border-t border-slate-400 pt-1">{{ $remission->received_by_name ?: '________________' }}</p>
            @if($remission->received_by_position)
            <p class="text-slate-500">{{ $remission->received_by_position }}</p>
            @endif
            @if($remission->received_by_document)
            <p class="text-slate-500">C.C. {{ $remission->received_by_document }}</p>
            @endif
        </div>
    </footer>
</div>
