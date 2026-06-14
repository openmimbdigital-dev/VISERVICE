<div class="relative mx-auto w-full max-w-[90rem]" x-data="{ activeTab: 'items' }">
    @php
    $statusBadge=['abierta'=>'bg-blue-50 text-blue-700 ring-blue-600/20','en_proceso'=>'bg-yellow-50 text-yellow-700 ring-yellow-600/20','finalizada'=>'bg-emerald-50 text-emerald-700 ring-emerald-600/20','cancelada'=>'bg-red-50 text-red-700 ring-red-600/20'];
    $badge=$statusBadge[$workOrder->status]??'bg-slate-100 text-slate-600 ring-slate-500/20';
    @endphp

    <nav class="mb-6 flex items-center gap-x-2 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.work-orders.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Órdenes de Trabajo</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $workOrder->reference }}</span>
    </nav>

    {{-- Header principal --}}
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-l-4 border-indigo-600 px-6 py-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="font-mono text-2xl font-bold text-slate-900">{{ $workOrder->reference }}</h1>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium ring-1 {{ $badge }}">
                            {{ $workOrder->status_label }}
                        </span>
                        @if($workOrder->quotation_id)
                        <a href="{{ route('admin.workshop.quotations.show', $workOrder->quotation_id) }}" wire:navigate
                            class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Desde cotización
                        </a>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-600">
                        <span><span class="font-medium text-slate-800">{{ $workOrder->client?->name }}</span></span>
                        <span>{{ $workOrder->vehicle?->plate }} — {{ $workOrder->vehicle?->brand }} {{ $workOrder->vehicle?->model }} {{ $workOrder->vehicle?->year }}</span>
                        <span>Km entrada: {{ number_format($workOrder->km_entry) }}</span>
                        @if($workOrder->estimated_delivery)
                        <span>Entrega est.: {{ $workOrder->estimated_delivery->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    @if($workOrder->diagnosis)
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">{{ $workOrder->diagnosis }}</p>
                    @endif
                </div>
                <div class="flex shrink-0 flex-col items-end gap-2 sm:items-end">
                    <div class="text-right">
                        <p class="text-xs text-slate-400">Total OT</p>
                        <p class="text-2xl font-bold text-indigo-700">{{ col_money($workOrder->total) }}</p>
                    </div>
                    <div class="flex gap-2">
                        @if($workOrder->status === 'abierta')
                        <button wire:click="startProcessing" wire:loading.attr="disabled" class="btn btn-warning btn-sm">
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Iniciar proceso
                        </button>
                        @endif
                        @if(in_array($workOrder->status, ['abierta','en_proceso']))
                        <button wire:click="openFinalizeModal" class="btn btn-success btn-sm">
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Finalizar OT
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="mb-4 flex gap-1 rounded-xl border border-slate-200/90 bg-white p-1 shadow-sm ring-1 ring-slate-900/[0.035]">
        @foreach([
            ['key'=>'items','label'=>'Items','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01','count'=>$workOrder->items->count()],
            ['key'=>'remisiones','label'=>'Remisiones','icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','count'=>$workOrder->remissions->count()],
            ['key'=>'facturas','label'=>'Factura','icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','count'=>$workOrder->invoices->count()],
            ['key'=>'compras','label'=>'Órd. Compra','icon'=>'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z','count'=>$workOrder->purchaseOrders->count()],
        ] as $tab)
        <button @click="activeTab='{{ $tab['key'] }}'"
            :class="activeTab==='{{ $tab['key'] }}' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
            class="flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/></svg>
            {{ $tab['label'] }}
            @if($tab['count'] > 0)
            <span :class="activeTab==='{{ $tab['key'] }}' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'"
                class="rounded-full px-2 py-0.5 text-xs tabular-nums">{{ $tab['count'] }}</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- TAB: ITEMS --}}
    <div x-show="activeTab==='items'" x-cloak>
        <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-3">
                <h2 class="font-semibold text-slate-900">Items de trabajo</h2>
                @if(!in_array($workOrder->status, ['finalizada','cancelada']))
                <button wire:click="openAddItem" class="btn btn-primary btn-sm">+ Agregar item</button>
                @endif
            </div>
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/40">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Tipo</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Descripción</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 uppercase">Cant.</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 uppercase">P. Unit.</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 uppercase">Desc.</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 uppercase">Subtotal</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-slate-500 uppercase">Estado item</th>
                        @if(!in_array($workOrder->status, ['finalizada','cancelada']))<th class="px-4 py-2"></th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                    $itemStatusBadge=['pendiente'=>'bg-slate-100 text-slate-600','en_proceso'=>'bg-yellow-50 text-yellow-700','completado'=>'bg-emerald-50 text-emerald-700','cancelado'=>'bg-red-50 text-red-600'];
                    @endphp
                    @forelse($workOrder->items as $item)
                    <tr wire:key="woi-{{ $item->id }}" class="{{ $item->status === 'cancelado' ? 'opacity-50' : '' }}">
                        <td class="px-4 py-2">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $item->item_type === 'servicio' ? 'bg-blue-50 text-blue-700' : ($item->item_type === 'repuesto' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ $item->item_type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm text-slate-900">
                            <p>{{ $item->description }}</p>
                            @if($item->technician_notes)<p class="text-xs text-slate-400 mt-0.5">{{ $item->technician_notes }}</p>@endif
                        </td>
                        <td class="px-4 py-2 text-right text-sm text-slate-600">{{ $item->quantity }}</td>
                        <td class="px-4 py-2 text-right text-sm text-slate-600">{{ col_money($item->unit_price) }}</td>
                        <td class="px-4 py-2 text-right text-sm text-slate-500">{{ $item->discount_percentage > 0 ? $item->discount_percentage.'%' : '—' }}</td>
                        <td class="px-4 py-2 text-right font-semibold text-slate-900">{{ col_money($item->subtotal) }}</td>
                        <td class="px-4 py-2 text-center">
                            @if(!in_array($workOrder->status, ['finalizada','cancelada']))
                            <select wire:change="updateItemStatus({{ $item->id }}, $event.target.value)"
                                class="rounded-lg border border-slate-200 px-2 py-1 text-xs {{ $itemStatusBadge[$item->status] ?? '' }}">
                                <option value="pendiente" {{ $item->status === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="en_proceso" {{ $item->status === 'en_proceso' ? 'selected' : '' }}>En proceso</option>
                                <option value="completado" {{ $item->status === 'completado' ? 'selected' : '' }}>Completado</option>
                                <option value="cancelado" {{ $item->status === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                            @else
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $itemStatusBadge[$item->status] ?? '' }}">{{ $item->status_label }}</span>
                            @endif
                        </td>
                        @if(!in_array($workOrder->status, ['finalizada','cancelada']))
                        <td class="px-4 py-2">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="openEditItem({{ $item->id }})" class="rounded p-1 text-slate-400 hover:text-indigo-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="deleteItem({{ $item->id }})" wire:confirm="¿Eliminar item?" class="rounded p-1 text-slate-400 hover:text-red-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-sm text-slate-400">Sin items. Haz clic en "+ Agregar item".</td></tr>
                    @endforelse
                </tbody>
                @if($workOrder->items->count() > 0)
                <tfoot class="border-t border-slate-200 bg-slate-50/60">
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500">Subtotal</td>
                        <td class="px-4 py-2 text-right font-semibold text-slate-700">{{ col_money($workOrder->subtotal) }}</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="px-4 py-1 text-right text-xs font-semibold uppercase text-slate-500">IVA {{ $workOrder->tax_percentage }}%</td>
                        <td class="px-4 py-1 text-right font-semibold text-slate-700">{{ col_money($workOrder->tax_amount) }}</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-right text-sm font-bold uppercase text-slate-900">Total</td>
                        <td class="px-4 py-2 text-right text-base font-bold text-indigo-700">{{ col_money($workOrder->total) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- TAB: REMISIONES --}}
    <div x-show="activeTab==='remisiones'" x-cloak>
        <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-3">
                <h2 class="font-semibold text-slate-900">Remisiones</h2>
                @if(!in_array($workOrder->status, ['cancelada']))
                <button wire:click="openRemissionModal" class="btn btn-primary btn-sm">+ Nueva remisión</button>
                @endif
            </div>
            @if($workOrder->remissions->isEmpty())
            <p class="px-4 py-8 text-center text-sm text-slate-400">No hay remisiones para esta OT.</p>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($workOrder->remissions as $rem)
                <div class="flex items-center justify-between px-5 py-4" wire:key="rem-{{ $rem->id }}">
                    <div>
                        <p class="font-mono font-semibold text-indigo-700">{{ $rem->reference }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Creada {{ $rem->created_at->format('d/m/Y H:i') }}
                            @if($rem->issued_at) &nbsp;·&nbsp; Emitida {{ $rem->issued_at->format('d/m/Y') }}@endif
                            @if($rem->delivered_at) &nbsp;·&nbsp; Entregada {{ $rem->delivered_at->format('d/m/Y') }}@endif
                        </p>
                        @if($rem->notes)<p class="mt-1 text-sm text-slate-600">{{ $rem->notes }}</p>@endif
                    </div>
                    <div class="flex items-center gap-3">
                        @php $rs=['borrador'=>'bg-slate-100 text-slate-600','emitida'=>'bg-blue-50 text-blue-700','entregada'=>'bg-emerald-50 text-emerald-700']; @endphp
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $rs[$rem->status]??'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($rem->status) }}
                        </span>
                        @if($rem->status !== 'entregada')
                        <select wire:change="updateRemissionStatus({{ $rem->id }}, $event.target.value)"
                            class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                            <option value="borrador" {{ $rem->status==='borrador'?'selected':'' }}>Borrador</option>
                            <option value="emitida" {{ $rem->status==='emitida'?'selected':'' }}>Emitida</option>
                            <option value="entregada" {{ $rem->status==='entregada'?'selected':'' }}>Entregada</option>
                        </select>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- TAB: FACTURA --}}
    <div x-show="activeTab==='facturas'" x-cloak>
        <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-3">
                <h2 class="font-semibold text-slate-900">Factura</h2>
                @if($workOrder->invoices->isEmpty() && !in_array($workOrder->status, ['cancelada']))
                <button wire:click="openInvoiceModal" class="btn btn-primary btn-sm">Generar factura</button>
                @endif
            </div>
            @if($workOrder->invoices->isEmpty())
            <p class="px-4 py-8 text-center text-sm text-slate-400">No hay factura generada para esta OT.
                @if(!in_array($workOrder->status, ['cancelada'])) Al finalizar la OT se genera automáticamente si hay saldo.@endif
            </p>
            @else
            @foreach($workOrder->invoices as $inv)
            @php $is=['pendiente'=>'bg-amber-50 text-amber-700','pagada'=>'bg-emerald-50 text-emerald-700','vencida'=>'bg-red-50 text-red-700','anulada'=>'bg-slate-100 text-slate-500']; @endphp
            <div class="p-6" wire:key="inv-{{ $inv->id }}">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <p class="font-mono text-xl font-bold text-slate-900">{{ $inv->reference }}</p>
                            <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium {{ $is[$inv->status]??'bg-slate-100 text-slate-600' }}">{{ $inv->status_label }}</span>
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-x-8 gap-y-1 text-sm">
                            <div><dt class="text-xs text-slate-400">Subtotal</dt><dd class="font-medium">{{ col_money($inv->subtotal) }}</dd></div>
                            <div><dt class="text-xs text-slate-400">IVA {{ $inv->tax_percentage }}%</dt><dd class="font-medium">{{ col_money($inv->tax_amount) }}</dd></div>
                            <div><dt class="text-xs text-slate-400">Total</dt><dd class="text-base font-bold text-indigo-700">{{ col_money($inv->total) }}</dd></div>
                            <div><dt class="text-xs text-slate-400">Vence</dt><dd>{{ $inv->due_date?->format('d/m/Y') ?? '—' }}</dd></div>
                            @if($inv->paid_at)
                            <div><dt class="text-xs text-slate-400">Pagada</dt><dd class="text-emerald-600 font-medium">{{ $inv->paid_at->format('d/m/Y') }}</dd></div>
                            <div><dt class="text-xs text-slate-400">Método</dt><dd>{{ $inv->payment_method }}</dd></div>
                            @endif
                        </dl>
                    </div>
                    @if($inv->status === 'pendiente')
                    <button wire:click="openPaymentModal({{ $inv->id }})" class="btn btn-success btn-sm shrink-0">
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Registrar pago
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    {{-- TAB: ÓRDENES DE COMPRA --}}
    <div x-show="activeTab==='compras'" x-cloak>
        <div class="space-y-4">
            <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-3">
                    <h2 class="font-semibold text-slate-900">Órdenes de compra</h2>
                    @if(!in_array($workOrder->status, ['finalizada','cancelada']))
                    <button wire:click="openPurchaseOrderModal" class="btn btn-primary btn-sm">+ Nueva OC</button>
                    @endif
                </div>
                @if($workOrder->purchaseOrders->isEmpty())
                <p class="px-4 py-8 text-center text-sm text-slate-400">No hay órdenes de compra para esta OT.</p>
                @else
                @php $ps=['borrador'=>'bg-slate-100 text-slate-600','enviada'=>'bg-blue-50 text-blue-700','recibida'=>'bg-emerald-50 text-emerald-700','cancelada'=>'bg-red-50 text-red-600']; @endphp
                <div class="divide-y divide-slate-100">
                    @foreach($workOrder->purchaseOrders as $po)
                    <div class="px-5 py-4" wire:key="po-{{ $po->id }}">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-semibold text-indigo-700">{{ $po->reference }}</span>
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $ps[$po->status]??'' }}">{{ ucfirst($po->status) }}</span>
                                </div>
                                <p class="mt-0.5 text-sm text-slate-600">{{ $po->supplier_name }}@if($po->supplier_nit) — NIT: {{ $po->supplier_nit }}@endif</p>
                                @if($po->expected_delivery)<p class="text-xs text-slate-400">Entrega esperada: {{ $po->expected_delivery->format('d/m/Y') }}</p>@endif
                            </div>
                            <p class="font-bold text-slate-900">{{ col_money($po->total) }}</p>
                        </div>
                        @if($po->items->count())
                        <table class="mt-3 min-w-full text-xs">
                            <thead><tr class="text-slate-400"><th class="py-1 text-left">Descripción</th><th class="py-1 text-right">Cant.</th><th class="py-1 text-right">P.Unit.</th><th class="py-1 text-right">Subtotal</th><th class="py-1 text-right">Recibido</th></tr></thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($po->items as $poi)
                                <tr wire:key="poi-{{ $poi->id }}">
                                    <td class="py-1 text-slate-700">{{ $poi->description }}</td>
                                    <td class="py-1 text-right text-slate-600">{{ $poi->quantity }}</td>
                                    <td class="py-1 text-right text-slate-600">{{ col_money($poi->unit_price) }}</td>
                                    <td class="py-1 text-right font-medium text-slate-900">{{ col_money($poi->subtotal) }}</td>
                                    <td class="py-1 text-right {{ $poi->pending_quantity > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $poi->received_quantity }}/{{ $poi->quantity }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODALES --}}
    {{-- ============================================================ --}}

    {{-- Modal agregar/editar item OT --}}
    <div x-show="$wire.showItemModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="closeItemModal"></div>
        <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="text-lg font-semibold text-slate-900">{{ $editing_item_id ? 'Editar item' : 'Agregar item' }}</h3>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <label class="label-up">Tipo</label>
                    <select wire:model.live="item_type" class="form-select">
                        <option value="servicio">Servicio</option>
                        <option value="repuesto">Repuesto</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="label-up">Desde catálogo</label>
                    <select wire:change="fillFromCatalog($event.target.value, '{{ $item_type }}')" class="form-select">
                        <option value="">Escribir manualmente</option>
                        @if($item_type === 'servicio')
                            @foreach($services as $svc)<option value="{{ $svc->id }}">{{ $svc->name }}</option>@endforeach
                        @else
                            @foreach($spare_parts as $sp)<option value="{{ $sp->id }}">{{ $sp->name }}</option>@endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="label-up">Descripción *</label>
                    <input type="text" wire:model="item_description" class="form-input" />
                    @error('item_description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label-up">Cantidad</label>
                    <input type="number" wire:model="item_quantity" class="form-input" min="0.01" step="0.01" />
                </div>
                <div>
                    <label class="label-up">Precio unitario</label>
                    <input type="number" wire:model="item_unit_price" class="form-input" min="0" step="100" />
                </div>
                <div>
                    <label class="label-up">Descuento (%)</label>
                    <input type="number" wire:model="item_discount" class="form-input" min="0" max="100" />
                </div>
                <div class="flex items-end">
                    @php $st = (float)$item_quantity * (float)$item_unit_price * (1 - (float)$item_discount/100); @endphp
                    <div class="w-full rounded-xl bg-indigo-50 px-4 py-3 text-center">
                        <p class="text-xs text-indigo-600">Subtotal</p>
                        <p class="text-lg font-bold text-indigo-700">{{ col_money($st) }}</p>
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="label-up">Notas del técnico</label>
                    <input type="text" wire:model="item_technician_notes" class="form-input" placeholder="Observaciones internas..." />
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="closeItemModal" class="btn btn-outline-secondary">Cancelar</button>
                <button wire:click="saveItem" wire:loading.attr="disabled" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>

    {{-- Modal finalizar OT --}}
    <div x-show="$wire.showFinalizeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showFinalizeModal', false)"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="text-lg font-semibold text-slate-900">Finalizar OT</h3>
            <p class="mt-1 text-sm text-slate-500">Si hay saldo pendiente, se generará la factura automáticamente.</p>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <label class="label-up">Km de salida</label>
                    <input type="number" wire:model="km_exit" class="form-input" min="0" />
                </div>
                <div class="col-span-2">
                    <label class="label-up">Descripción del trabajo realizado</label>
                    <textarea wire:model="finalize_work_description" class="form-input" rows="4"
                        placeholder="Resumen de los trabajos realizados, repuestos cambiados..."></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('showFinalizeModal', false)" class="btn btn-outline-secondary">Cancelar</button>
                <button wire:click="finalizeWorkOrder" wire:loading.attr="disabled" class="btn btn-success">Confirmar y Finalizar</button>
            </div>
        </div>
    </div>

    {{-- Modal remisión --}}
    <div x-show="$wire.showRemissionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showRemissionModal', false)"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="text-lg font-semibold text-slate-900">Nueva Remisión</h3>
            <p class="mt-1 text-sm text-slate-500">Se incluirán los items de la OT en la remisión.</p>
            <div class="mt-4">
                <label class="label-up">Notas (opcional)</label>
                <textarea wire:model="remission_notes" class="form-input" rows="3" placeholder="Observaciones de entrega..."></textarea>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('showRemissionModal', false)" class="btn btn-outline-secondary">Cancelar</button>
                <button wire:click="createRemission" wire:loading.attr="disabled" class="btn btn-primary">Crear remisión</button>
            </div>
        </div>
    </div>

    {{-- Modal generar factura --}}
    <div x-show="$wire.showInvoiceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showInvoiceModal', false)"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="text-lg font-semibold text-slate-900">Generar Factura</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="label-up">Fecha de vencimiento</label>
                    <input type="date" wire:model="invoice_due_date" class="form-input" />
                </div>
                <div>
                    <label class="label-up">Notas</label>
                    <textarea wire:model="invoice_notes" class="form-input" rows="2"></textarea>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Total a facturar</span><span class="font-bold text-indigo-700">{{ col_money($workOrder->total) }}</span></div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('showInvoiceModal', false)" class="btn btn-outline-secondary">Cancelar</button>
                <button wire:click="generateInvoice" wire:loading.attr="disabled" class="btn btn-primary">Generar</button>
            </div>
        </div>
    </div>

    {{-- Modal registrar pago --}}
    <div x-show="$wire.showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showPaymentModal', false)"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="text-lg font-semibold text-slate-900">Registrar Pago</h3>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="label-up">Método de pago *</label>
                    <select wire:model="payment_method" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta_debito">Tarjeta débito</option>
                        <option value="tarjeta_credito">Tarjeta crédito</option>
                        <option value="nequi">Nequi</option>
                        <option value="daviplata">Daviplata</option>
                        <option value="cheque">Cheque</option>
                    </select>
                    @error('payment_method')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label-up">Fecha de pago</label>
                    <input type="date" wire:model="payment_date" class="form-input" />
                </div>
                <div>
                    <label class="label-up">Referencia / # comprobante</label>
                    <input type="text" wire:model="payment_reference" class="form-input" placeholder="Ref. transacción..." />
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('showPaymentModal', false)" class="btn btn-outline-secondary">Cancelar</button>
                <button wire:click="registerPayment" wire:loading.attr="disabled" class="btn btn-success">Confirmar pago</button>
            </div>
        </div>
    </div>

    {{-- Modal orden de compra --}}
    <div x-show="$wire.showPurchaseOrderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showPurchaseOrderModal', false)"></div>
        <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="text-lg font-semibold text-slate-900">Nueva Orden de Compra</h3>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <label class="label-up">Proveedor *</label>
                    <input type="text" wire:model="po_supplier_name" class="form-input" placeholder="Nombre del proveedor" />
                    @error('po_supplier_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label-up">NIT proveedor</label>
                    <input type="text" wire:model="po_supplier_nit" class="form-input" />
                </div>
                <div>
                    <label class="label-up">Teléfono proveedor</label>
                    <input type="text" wire:model="po_supplier_phone" class="form-input" />
                </div>
                <div>
                    <label class="label-up">Entrega esperada</label>
                    <input type="date" wire:model="po_expected_delivery" class="form-input" />
                </div>
                <div class="col-span-2">
                    <label class="label-up">Notas</label>
                    <textarea wire:model="po_notes" class="form-input" rows="2"></textarea>
                </div>
            </div>

            {{-- Items OC --}}
            <div class="mt-5">
                <div class="flex items-center justify-between">
                    <h4 class="font-medium text-slate-900">Items</h4>
                    <button wire:click="addPOItem" type="button" class="btn btn-outline-secondary btn-sm">+ Agregar item</button>
                </div>
                <div class="mt-2 space-y-2">
                    @foreach($po_items as $i => $poi)
                    <div class="grid grid-cols-12 gap-2 items-end" wire:key="poi-new-{{ $i }}">
                        <div class="col-span-5">
                            @if($i === 0)<label class="label-up">Descripción *</label>@endif
                            <input type="text" wire:model="po_items.{{ $i }}.description" class="form-input" />
                        </div>
                        <div class="col-span-2">
                            @if($i === 0)<label class="label-up">Cant.</label>@endif
                            <input type="number" wire:model="po_items.{{ $i }}.quantity" class="form-input" min="1" step="1" />
                        </div>
                        <div class="col-span-3">
                            @if($i === 0)<label class="label-up">P. Unit.</label>@endif
                            <input type="number" wire:model="po_items.{{ $i }}.unit_price" class="form-input" min="0" step="100" />
                        </div>
                        <div class="col-span-1 pb-2 text-right text-xs font-medium text-slate-700">
                            {{ col_money((float)($poi['quantity']??0) * (float)($poi['unit_price']??0)) }}
                        </div>
                        <div class="col-span-1 flex justify-end pb-1">
                            @if($i > 0)
                            <button wire:click="removePOItem({{ $i }})" type="button" class="rounded p-1 text-red-400 hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('showPurchaseOrderModal', false)" class="btn btn-outline-secondary">Cancelar</button>
                <button wire:click="savePurchaseOrder" wire:loading.attr="disabled" class="btn btn-primary">Crear OC</button>
            </div>
        </div>
    </div>
</div>
