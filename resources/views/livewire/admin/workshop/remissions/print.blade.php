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
            <p class="font-semibold uppercase text-slate-500">Equipo / OT</p>
            <p>{{ $remission->equipment?->select_label ?? '—' }}</p>
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
        @if(! empty($document_client))
        <div>
            <p class="font-semibold uppercase text-slate-500">Documento del cliente</p>
            @foreach($document_client as $label => $value)
            <p><span class="text-slate-500">{{ $document_labels[$label] ?? $label }}:</span> {{ $value }}</p>
            @endforeach
        </div>
        @endif
    </section>

    <table class="mt-4 w-full border-collapse text-xs">
        <thead>
            <tr class="border-b border-slate-300 bg-slate-50">
                <th class="px-2 py-1 text-left">#</th>
                <th class="px-2 py-1 text-left">Descripción</th>
                <th class="px-2 py-1 text-left">Tipo</th>
                <th class="px-2 py-1 text-left">Categoría</th>
                <th class="px-2 py-1 text-left">Ref./Marca</th>
                <th class="px-2 py-1 text-right">Cant.</th>
                <th class="px-2 py-1 text-left">Unidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($remission->items as $index => $item)
            <tr class="border-b border-slate-100">
                <td class="px-2 py-1">{{ $index + 1 }}</td>
                <td class="px-2 py-1">
                    {{ $item->description }}
                    @if($item->observations)
                    <span class="block text-slate-500">{{ $item->observations }}</span>
                    @endif
                </td>
                <td class="px-2 py-1">{{ $item->productType?->name ?? '—' }}</td>
                <td class="px-2 py-1">{{ $item->productCategory?->name ?? '—' }}</td>
                <td class="px-2 py-1">{{ $item->reference_brand ?? '—' }}</td>
                <td class="px-2 py-1 text-right font-medium">{{ $item->quantity }}</td>
                <td class="px-2 py-1">{{ $item->unit_name ?? $item->unit?->name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t border-slate-300">
                <td colspan="5" class="px-2 py-2 text-right font-semibold">Total ítems</td>
                <td class="px-2 py-2 text-right font-bold">{{ $remission->total_items }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

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
