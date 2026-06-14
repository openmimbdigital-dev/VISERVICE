<div class="relative mx-auto w-full max-w-[90rem]">
    @php
    $statusBadge=['borrador'=>'bg-slate-100 text-slate-600','enviada'=>'bg-blue-50 text-blue-700','aceptada'=>'bg-emerald-50 text-emerald-700','rechazada'=>'bg-red-50 text-red-700','vencida'=>'bg-orange-50 text-orange-700'];
    $badge=$statusBadge[$quotation->status]??'bg-slate-100 text-slate-600';
    @endphp

    <nav class="mb-6 flex items-center gap-x-2 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.quotations.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Cotizaciones</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $quotation->reference }}</span>
    </nav>

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $quotation->reference }}</h1>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $badge }}">{{ $quotation->status_label }}</span>
            </div>
            <p class="mt-1 text-sm text-slate-500">
                {{ $quotation->client?->name }} &nbsp;·&nbsp; {{ $quotation->vehicle?->plate }} {{ $quotation->vehicle?->brand }} {{ $quotation->vehicle?->model }}
                &nbsp;·&nbsp; Km: {{ number_format($quotation->km_entry) }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(in_array($quotation->status, ['borrador','enviada']))
                <button wire:click="sendQuotation" class="btn btn-secondary btn-sm">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Enviar al cliente
                </button>
            @endif
            @if($quotation->status === 'enviada')
                <button wire:click="acceptQuotation" wire:loading.attr="disabled" class="btn btn-success btn-sm">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Aceptar → Crear OT
                </button>
                <button wire:click="openRejectModal" class="btn btn-danger btn-sm">Rechazar</button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Columna items --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-3">
                    <h2 class="font-semibold text-slate-900">Items de la cotización</h2>
                    @if(!in_array($quotation->status, ['aceptada','rechazada','vencida']))
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
                            @if(!in_array($quotation->status, ['aceptada','rechazada','vencida']))<th class="px-4 py-2"></th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($quotation->items as $item)
                        <tr wire:key="qi-{{ $item->id }}">
                            <td class="px-4 py-2">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $item->item_type === 'servicio' ? 'bg-blue-50 text-blue-700' : ($item->item_type === 'repuesto' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $item->item_type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm text-slate-900">{{ $item->description }}</td>
                            <td class="px-4 py-2 text-right text-sm text-slate-600">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-right text-sm text-slate-600">{{ col_money($item->unit_price) }}</td>
                            <td class="px-4 py-2 text-right text-sm text-slate-500">{{ $item->discount_percentage > 0 ? $item->discount_percentage . '%' : '—' }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-slate-900">{{ col_money($item->subtotal) }}</td>
                            @if(!in_array($quotation->status, ['aceptada','rechazada','vencida']))
                            <td class="px-4 py-2">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEditItem({{ $item->id }})" class="rounded p-1 text-slate-400 hover:text-indigo-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="deleteItem({{ $item->id }})" wire:confirm="¿Eliminar este item?" class="rounded p-1 text-slate-400 hover:text-red-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-slate-400">Sin items. Haz clic en "Agregar item" para comenzar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Columna resumen --}}
        <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035]">
                <h3 class="font-semibold text-slate-900">Resumen</h3>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-medium">{{ col_money($quotation->subtotal) }}</dd></div>
                    <div class="flex justify-between items-center">
                        <dt class="text-slate-500">IVA</dt>
                        <dd class="flex items-center gap-2">
                            @if(!in_array($quotation->status, ['aceptada','rechazada','vencida']))
                            <input type="number" wire:change="updateTaxPercentage($event.target.value)" value="{{ $quotation->tax_percentage }}" min="0" max="100" class="w-16 rounded border border-slate-300 px-2 py-0.5 text-right text-sm" />
                            <span class="text-slate-400">%</span>
                            @else
                            <span>{{ $quotation->tax_percentage }}%</span>
                            @endif
                            <span class="font-medium">{{ col_money($quotation->tax_amount) }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-bold">
                        <dt class="text-slate-900">Total</dt><dd class="text-indigo-700">{{ col_money($quotation->total) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035]">
                <h3 class="font-semibold text-slate-900">Detalles</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="text-xs text-slate-400">Creada</dt><dd>{{ $quotation->created_at->format('d/m/Y H:i') }}</dd></div>
                    @if($quotation->valid_until)
                    <div><dt class="text-xs text-slate-400">Válida hasta</dt><dd>{{ $quotation->valid_until->format('d/m/Y') }}</dd></div>
                    @endif
                    @if($quotation->sent_at)
                    <div><dt class="text-xs text-slate-400">Enviada</dt><dd>{{ $quotation->sent_at->format('d/m/Y H:i') }}</dd></div>
                    @endif
                    @if($quotation->accepted_at)
                    <div><dt class="text-xs text-slate-400">Aceptada</dt><dd class="text-emerald-600 font-medium">{{ $quotation->accepted_at->format('d/m/Y H:i') }}</dd></div>
                    @endif
                    @if($quotation->workOrder)
                    <div class="pt-2 border-t border-slate-100">
                        <dt class="text-xs text-slate-400">OT generada</dt>
                        <dd>
                            <a href="{{ route('admin.workshop.work-orders.show', $quotation->workOrder->id) }}" wire:navigate
                                class="font-mono text-sm font-semibold text-indigo-600 hover:underline">
                                {{ $quotation->workOrder->reference }}
                            </a>
                        </dd>
                    </div>
                    @endif
                    @if($quotation->diagnosis)
                    <div class="pt-2 border-t border-slate-100"><dt class="text-xs text-slate-400">Diagnóstico</dt><dd class="mt-1 text-slate-700">{{ $quotation->diagnosis }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    {{-- Modal agregar/editar item --}}
    <div x-show="$wire.showItemModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="closeItemModal"></div>
        <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="text-lg font-semibold text-slate-900">{{ $editing_item_id ? 'Editar item' : 'Agregar item' }}</h3>
            <div class="mt-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label-up">Tipo</label>
                        <select wire:model="item_type" class="form-select">
                            <option value="servicio">Servicio</option>
                            <option value="repuesto">Repuesto</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-up">Desde catálogo</label>
                        <select wire:model.live="catalog_item_id" class="form-select"
                            wire:change="fillFromCatalog($event.target.value, '{{ $item_type }}')">
                            <option value="">Escribir manualmente</option>
                            @if($item_type === 'servicio')
                                @foreach($services as $svc)
                                    <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                                @endforeach
                            @else
                                @foreach($spare_parts as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                @endforeach
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
                            <p class="text-xs text-indigo-600">Subtotal estimado</p>
                            <p class="text-lg font-bold text-indigo-700">{{ col_money($st) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="closeItemModal" class="btn btn-outline-secondary">Cancelar</button>
                <button wire:click="saveItem" wire:loading.attr="disabled" class="btn btn-primary">Guardar item</button>
            </div>
        </div>
    </div>

    {{-- Modal rechazar --}}
    <div x-show="$wire.showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showRejectModal', false)"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="text-lg font-semibold text-red-700">Rechazar cotización</h3>
            <p class="mt-1 text-sm text-slate-500">Esta acción no se puede deshacer.</p>
            <div class="mt-4">
                <label class="label-up">Motivo del rechazo (opcional)</label>
                <textarea wire:model="reject_reason" class="form-input" rows="3" placeholder="Precio muy alto, cliente no disponible..."></textarea>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('showRejectModal', false)" class="btn btn-outline-secondary">Cancelar</button>
                <button wire:click="rejectQuotation" class="btn btn-danger">Confirmar rechazo</button>
            </div>
        </div>
    </div>
</div>
