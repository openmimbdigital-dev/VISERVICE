@php
    $business = $quotation->business;
    $subtotals = $category_subtotals;
@endphp
<div class="mx-auto max-w-4xl p-6 text-sm text-slate-800">
    {{-- Encabezado negocio --}}
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
                <p class="text-lg font-bold text-indigo-700">COTIZACIÓN</p>
                <p class="font-mono text-base font-semibold">{{ $quotation->reference }}</p>
                <p class="text-xs text-slate-500">Fecha: {{ ($quotation->issued_at ?? $quotation->created_at)->format('d/m/Y') }}</p>
                @if($quotation->valid_until)
                <p class="text-xs">Vigencia: {{ $quotation->valid_until->format('d/m/Y') }} ({{ $quotation->validity_days }} días)</p>
                @endif
            </div>
        </div>
    </header>

    {{-- Cliente y equipo --}}
    <section class="mt-4 grid grid-cols-2 gap-4 text-xs">
        <div>
            <p class="font-semibold uppercase text-slate-500">Cliente</p>
            <p class="font-medium">{{ $quotation->client?->name }}</p>
            <p>{{ $quotation->client?->document_number }}</p>
            <p>{{ $quotation->client?->phone }}</p>
        </div>
        <div>
            <p class="font-semibold uppercase text-slate-500">Equipos</p>
            @forelse($quotation->equipments as $equipment)
                <p>{{ $equipment->select_label }}</p>
            @empty
                <p>—</p>
            @endforelse
            @if($quotation->hours_entry_formatted)
            <p>Horas al ingreso: {{ $quotation->hours_entry_formatted }}</p>
            @endif
            @if($quotation->quotationServiceType)
            <p>Tipo servicio: {{ $quotation->quotationServiceType->name }}</p>
            @endif
        </div>
    </section>

    @if($quotation->diagnosis)
    <section class="mt-3 rounded border border-slate-200 p-2 text-xs">
        <p class="font-semibold">Diagnóstico</p>
        <p>{{ $quotation->diagnosis }}</p>
    </section>
    @endif

    {{-- Ítems --}}
    <table class="mt-4 w-full border-collapse text-xs">
        <thead>
            <tr class="border-b border-slate-300 bg-slate-50">
                <th class="px-2 py-1 text-left">Equipo</th>
                <th class="px-2 py-1 text-left">Tipo</th>
                <th class="px-2 py-1 text-left">Categoría</th>
                <th class="px-2 py-1 text-left">Descripción</th>
                <th class="px-2 py-1 text-right">Cant.</th>
                <th class="px-2 py-1 text-right">P. Unit.</th>
                <th class="px-2 py-1 text-right">Desc.</th>
                <th class="px-2 py-1 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
            <tr class="border-b border-slate-100">
                <td class="px-2 py-1">{{ $item->equipment?->select_label ?? '—' }}</td>
                <td class="px-2 py-1">{{ $item->productType?->name ?? '—' }}</td>
                <td class="px-2 py-1">{{ $item->productCategory?->name ?? '—' }}</td>
                <td class="px-2 py-1">{{ $item->description }}</td>
                <td class="px-2 py-1 text-right">{{ $item->quantity }}</td>
                <td class="px-2 py-1 text-right">{{ col_money($item->unit_price) }}</td>
                <td class="px-2 py-1 text-right">{{ $item->discount_percentage > 0 ? $item->discount_percentage.'%' : '—' }}</td>
                <td class="px-2 py-1 text-right font-medium">{{ col_money($item->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Subtotales por categoría --}}
    <section class="mt-4 grid grid-cols-2 gap-x-8 gap-y-1 text-xs sm:ml-auto sm:max-w-sm">
        <p>Subtotal Mano de Obra</p><p class="text-right font-medium">{{ col_money($subtotals['mano_obra']) }}</p>
        <p>Subtotal Repuestos</p><p class="text-right font-medium">{{ col_money($subtotals['repuestos']) }}</p>
        <p>Subtotal Lubricantes</p><p class="text-right font-medium">{{ col_money($subtotals['lubricantes']) }}</p>
        <p>Subtotal Otros materiales</p><p class="text-right font-medium">{{ col_money($subtotals['otros']) }}</p>
        <p class="border-t border-slate-200 pt-1">Subtotal</p>
        <p class="border-t border-slate-200 pt-1 text-right font-medium">{{ col_money($quotation->subtotal) }}</p>
        <p>IVA ({{ $quotation->tax_percentage }}%)</p>
        <p class="text-right font-medium">{{ col_money($quotation->tax_amount) }}</p>
        <p class="text-base font-bold">TOTAL</p>
        <p class="text-right text-base font-bold text-indigo-700">{{ col_money($quotation->total) }}</p>
    </section>

    {{-- Condiciones --}}
    <section class="mt-6 space-y-2 text-xs">
        @if($quotation->execution_time)
        <p><strong>Tiempo de ejecución:</strong> {{ $quotation->execution_time }}</p>
        @endif
        @if($quotation->paymentMethod)
        <p><strong>Forma de pago:</strong> {{ $quotation->paymentMethod->name }}</p>
        @endif
        @if($quotation->observations)
        <p><strong>Observaciones:</strong> {{ $quotation->observations }}</p>
        @endif
    </section>

    {{-- Datos bancarios --}}
    @if($quotation->bankAccount)
    <section class="mt-4 rounded border border-slate-200 p-3 text-xs">
        <p class="font-semibold">Datos para consignación</p>
        <p>{{ $quotation->bankAccount->bank_name }} — {{ $quotation->bankAccount->accountTypeLabel() }}</p>
        <p>Cuenta: {{ $quotation->bankAccount->account_number }}</p>
        <p>Titular: {{ $quotation->bankAccount->account_holder }} ({{ $quotation->bankAccount->document_type }} {{ $quotation->bankAccount->document_number }})</p>
    </section>
    @endif

    {{-- Firmas --}}
    <footer class="mt-10 grid grid-cols-2 gap-8 text-xs">
        <div>
            <p class="font-semibold">Cotizado por</p>
            <p class="mt-6 border-t border-slate-400 pt-1">{{ $quotation->createdBy?->full_name ?? $quotation->createdBy?->username }}</p>
        </div>
        @if($quotation->approved_by_name)
        <div>
            <p class="font-semibold">Aprobado por el cliente</p>
            <p class="mt-6 border-t border-slate-400 pt-1">{{ $quotation->approved_by_name }}</p>
            @if($quotation->approved_by_position)<p class="text-slate-500">{{ $quotation->approved_by_position }}</p>@endif
        </div>
        @endif
    </footer>
</div>
