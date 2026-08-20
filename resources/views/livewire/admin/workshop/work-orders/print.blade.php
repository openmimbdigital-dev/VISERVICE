@php
    $business = $workOrder->business;
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
                <p class="text-lg font-bold text-indigo-700">ORDEN DE TRABAJO</p>
                <p class="font-mono text-base font-semibold">{{ $workOrder->reference }}</p>
                <p class="text-xs text-slate-500">Estado: {{ $workOrder->status_label }}</p>
                <p class="text-xs text-slate-500">Fecha: {{ $workOrder->created_at->format('d/m/Y') }}</p>
                @if($workOrder->estimated_delivery)
                <p class="text-xs text-slate-500">Entrega est.: {{ $workOrder->estimated_delivery->format('d/m/Y') }}</p>
                @endif
                @if($workOrder->quotation)
                <p class="text-xs">Cotización: {{ $workOrder->quotation->reference }}</p>
                @endif
            </div>
        </div>
    </header>

    <section class="mt-4 grid grid-cols-2 gap-4 text-xs">
        <div>
            <p class="font-semibold uppercase text-slate-500">Cliente</p>
            <p class="font-medium">{{ $workOrder->client?->name }}</p>
            @if($workOrder->client?->document_number)
            <p>{{ $workOrder->client->document_type }} {{ $workOrder->client->document_number }}</p>
            @endif
            @if($workOrder->client?->phone)
            <p>{{ $workOrder->client->phone }}</p>
            @endif
        </div>
        <div>
            <p class="font-semibold uppercase text-slate-500">Equipos</p>
            @forelse($workOrder->equipments as $equipment)
                <p>{{ $equipment->select_label }}</p>
            @empty
                <p>—</p>
            @endforelse
        </div>
    </section>

    @if(! empty($document_client))
    <section class="mt-3 rounded border border-slate-200 p-2 text-xs">
        <p class="font-semibold">Documento del cliente</p>
        @foreach($document_client as $label => $value)
        <p><span class="text-slate-500">{{ $document_labels[$label] ?? $label }}:</span> {{ $value }}</p>
        @endforeach
    </section>
    @endif

    @if($workOrder->diagnosis)
    <section class="mt-3 rounded border border-slate-200 p-2 text-xs">
        <p class="font-semibold">Diagnóstico</p>
        <p>{{ $workOrder->diagnosis }}</p>
    </section>
    @endif

    @if($workOrder->work_description)
    <section class="mt-3 rounded border border-slate-200 p-2 text-xs">
        <p class="font-semibold">Descripción del trabajo</p>
        <p>{{ $workOrder->work_description }}</p>
    </section>
    @endif

    <table class="mt-4 w-full border-collapse text-xs">
        <thead>
            <tr class="border-b border-slate-300 bg-slate-50">
                <th class="px-2 py-1 text-left">#</th>
                <th class="px-2 py-1 text-left">Equipo</th>
                <th class="px-2 py-1 text-left">Descripción</th>
                <th class="px-2 py-1 text-left">Tipo</th>
                <th class="px-2 py-1 text-right">Cant.</th>
                <th class="px-2 py-1 text-right">P. Unit.</th>
                <th class="px-2 py-1 text-right">Desc.</th>
                <th class="px-2 py-1 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($workOrder->items as $index => $item)
            <tr class="border-b border-slate-100">
                <td class="px-2 py-1">{{ $index + 1 }}</td>
                <td class="px-2 py-1">{{ $item->equipment?->select_label ?? '—' }}</td>
                <td class="px-2 py-1">
                    {{ $item->description }}
                    @if($item->technician_notes)
                    <span class="block text-slate-500">{{ $item->technician_notes }}</span>
                    @endif
                </td>
                <td class="px-2 py-1">{{ $item->productType?->name ?? '—' }}</td>
                <td class="px-2 py-1 text-right">{{ $item->quantity }}</td>
                <td class="px-2 py-1 text-right">{{ col_money($item->unit_price) }}</td>
                <td class="px-2 py-1 text-right">{{ $item->discount_percentage > 0 ? $item->discount_percentage.'%' : '—' }}</td>
                <td class="px-2 py-1 text-right font-medium">{{ col_money($item->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <section class="mt-4 ml-auto max-w-xs space-y-1 text-xs">
        <div class="flex justify-between"><span>Subtotal</span><span class="font-medium">{{ col_money($workOrder->subtotal) }}</span></div>
        <div class="flex justify-between"><span>IVA ({{ $workOrder->tax_percentage }}%)</span><span class="font-medium">{{ col_money($workOrder->tax_amount) }}</span></div>
        <div class="flex justify-between border-t border-slate-200 pt-1 text-base font-bold text-indigo-700">
            <span>TOTAL</span><span>{{ col_money($workOrder->total) }}</span>
        </div>
    </section>

    @if($workOrder->observations || $workOrder->notes)
    <section class="mt-4 space-y-1 text-xs">
        @if($workOrder->observations)
        <p><strong>Observaciones:</strong> {{ $workOrder->observations }}</p>
        @endif
        @if($workOrder->notes)
        <p><strong>Notas:</strong> {{ $workOrder->notes }}</p>
        @endif
    </section>
    @endif

    <footer class="mt-10 grid grid-cols-2 gap-8 text-xs">
        <div>
            <p class="font-semibold">Elaborado por</p>
            <p class="mt-6 border-t border-slate-400 pt-1">{{ $workOrder->createdBy?->full_name ?? $workOrder->createdBy?->username }}</p>
        </div>
        <div>
            <p class="font-semibold">Recibido / Autorizado</p>
            <p class="mt-6 border-t border-slate-400 pt-1">&nbsp;</p>
        </div>
    </footer>
</div>
