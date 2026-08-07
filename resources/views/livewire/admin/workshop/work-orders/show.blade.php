<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.work-orders.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Órdenes de Trabajo</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $workOrder->reference }}</span>
    </nav>

    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-l-4 border-indigo-600 px-4 py-5 sm:px-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="font-mono text-2xl font-bold text-slate-900">{{ $workOrder->reference }}</h1>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $status_badge_class }}">
                            {{ $workOrder->status_label }}
                        </span>
                        @if($workOrder->quotation_id)
                        <a href="{{ route('admin.workshop.quotations.show', $workOrder->quotation_id) }}" wire:navigate
                            class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                            Desde cotización
                        </a>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-600">
                        <span class="font-medium text-slate-800">{{ $workOrder->client?->name }}</span>
                        <span>{{ $workOrder->equipment?->plate }} — {{ $workOrder->equipment?->brand_name }} {{ $workOrder->equipment?->model_name }}</span>
                        @if($workOrder->estimated_delivery)
                        <span>Entrega est.: {{ $workOrder->estimated_delivery->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    @if($workOrder->diagnosis)
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">{{ $workOrder->diagnosis }}</p>
                    @endif
                    @if($edit_disabled)
                    <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Esta OT está {{ strtolower($workOrder->status_label) }}; no se puede editar ni cambiar de estado.
                    </p>
                    @endif
                </div>
                <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:items-end">
                    <div class="text-left sm:text-right">
                        <p class="text-xs text-slate-400">Total OT</p>
                        <p class="text-2xl font-bold text-indigo-700">{{ col_money($workOrder->total) }}</p>
                        @if((float) $workOrder->advance_amount > 0)
                        <p class="mt-1 text-xs text-amber-700">
                            Anticipo ({{ rtrim(rtrim(number_format((float) $workOrder->advance_percentage, 2, '.', ''), '0'), '.') }}%):
                            <span class="font-semibold">{{ col_money($workOrder->advance_amount) }}</span>
                        </p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2 sm:justify-end">
                        <a href="{{ route('admin.workshop.work-orders.print', $workOrder) }}" target="_blank"
                            class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                            Imprimir / PDF
                        </a>
                        @if($can_edit)
                        <button type="button"
                            wire:click="openDocumentModal"
                            title="Registrar documento asociado"
                            class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                            Documento asociado
                        </button>
                        @if($edit_disabled)
                        <button type="button" disabled title="{{ $edit_disabled_title }}"
                            class="btn btn-outline-secondary btn-sm flex-1 justify-center opacity-50 sm:flex-none">
                            Editar
                        </button>
                        @else
                        <a href="{{ route('admin.workshop.work-orders.form.edit', $workOrder) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                            Editar
                        </a>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
    @if(! empty($workOrder->document_client))
    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
            <h2 class="font-semibold text-slate-900">Documentos del cliente</h2>
        </div>
        <dl class="divide-y divide-slate-100 px-4 py-2 sm:px-5">
            @foreach($workOrder->document_client as $label => $value)
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">
                    {{ $associated_documents->firstWhere('label', $label)?->value ?? $label }}
                </dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $value }}</dd>
            </div>
            @endforeach
        </dl>
    </section>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <h2 class="font-semibold text-slate-900">Ítems de trabajo</h2>
            @if($can_manage)
            <button wire:click="openAddItem" class="btn btn-primary btn-sm w-full justify-center sm:w-auto">+ Agregar ítem</button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/40">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">Tipo</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">Descripción</th>
                        <th class="hidden px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 sm:table-cell sm:px-4">Cant.</th>
                        <th class="hidden px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 md:table-cell sm:px-4">P. Unit.</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 sm:px-4">Subtotal</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold uppercase text-slate-500 sm:px-4">Completados</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold uppercase text-slate-500 sm:px-4">Cancelados</th>
                        @if($can_manage)
                        <th class="px-3 py-2 text-center text-xs font-semibold uppercase text-slate-500 sm:px-4">Avance</th>
                        <th class="px-3 py-2 sm:px-4"></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workOrder->items as $item)
                    @php
                        $item_qty = (float) $item->quantity;
                        $item_complete = (float) $item->quantity_complete;
                        $item_canceled = (float) $item->quantity_canceled;
                        $can_complete = $item_complete < $item_qty;
                        $can_cancel = $item_canceled < $item_qty;
                    @endphp
                    <tr wire:key="woi-{{ $item->id }}">
                        <td class="px-3 py-3 sm:px-4">
                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                {{ $item->productType?->name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-sm text-slate-900 sm:px-4">
                            <p>{{ $item->description }}</p>
                            @if($item->technician_notes)
                            <p class="mt-0.5 text-xs text-slate-400">{{ $item->technician_notes }}</p>
                            @endif
                        </td>
                        <td class="hidden px-3 py-3 text-right text-sm text-slate-600 sm:table-cell sm:px-4">{{ $item->quantity }}</td>
                        <td class="hidden px-3 py-3 text-right text-sm text-slate-600 md:table-cell sm:px-4">{{ col_money($item->unit_price) }}</td>
                        <td class="px-3 py-3 text-right font-semibold text-slate-900 sm:px-4">{{ col_money($item->subtotal) }}</td>
                        <td class="px-3 py-3 text-center text-sm font-semibold text-emerald-700 sm:px-4">{{ $item->quantity_complete + 0 }}</td>
                        <td class="px-3 py-3 text-center text-sm font-semibold text-rose-600 sm:px-4">{{ $item->quantity_canceled + 0 }}</td>
                        @if($can_manage)
                        <td class="px-3 py-3 sm:px-4">
                            <div class="flex flex-wrap items-center justify-center gap-1">
                                <button type="button"
                                    wire:click="completeItemQuantity({{ $item->id }})"
                                    @disabled(! $can_complete)
                                    class="inline-flex items-center justify-center rounded-lg bg-emerald-50 p-1.5 text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-40"
                                    title="Completar +1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button"
                                    wire:click="cancelItemQuantity({{ $item->id }})"
                                    @disabled(! $can_cancel)
                                    class="inline-flex items-center justify-center rounded-lg bg-rose-50 p-1.5 text-rose-700 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-40"
                                    title="Cancelar +1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-3 py-3 sm:px-4">
                            <div class="flex flex-wrap justify-end gap-1">
                                <button wire:click="openEditItem({{ $item->id }})" type="button" class="rounded p-1 text-slate-400 hover:text-indigo-600" title="Editar">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="deleteItem({{ $item->id }})" wire:confirm="¿Eliminar ítem?" type="button" class="rounded p-1 text-slate-400 hover:text-red-600" title="Eliminar">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $can_manage ? 9 : 7 }}" class="px-4 py-8 text-center text-sm text-slate-400">Sin ítems. Usa «Agregar ítem».</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($workOrder->items->isNotEmpty())
                <tfoot class="border-t border-slate-200 bg-slate-50/60">
                    <tr>
                        <td colspan="4" class="hidden px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 md:table-cell">Subtotal</td>
                        <td colspan="2" class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 md:hidden">Subtotal</td>
                        <td class="px-4 py-2 text-right font-semibold text-slate-700">{{ col_money($workOrder->subtotal) }}</td>
                        <td colspan="{{ $can_manage ? 4 : 2 }}"></td>
                    </tr>
                    @if((float) $workOrder->advance_amount > 0)
                    <tr>
                        <td colspan="4" class="hidden px-4 py-1 text-right text-xs font-semibold uppercase text-amber-700 md:table-cell">Anticipo {{ $workOrder->advance_percentage }}%</td>
                        <td colspan="2" class="px-4 py-1 text-right text-xs font-semibold uppercase text-amber-700 md:hidden">Anticipo {{ $workOrder->advance_percentage }}%</td>
                        <td class="px-4 py-1 text-right font-semibold text-amber-700">{{ col_money($workOrder->advance_amount) }}</td>
                        <td colspan="{{ $can_manage ? 4 : 2 }}"></td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="4" class="hidden px-4 py-1 text-right text-xs font-semibold uppercase text-slate-500 md:table-cell">IVA {{ $workOrder->tax_percentage }}%</td>
                        <td colspan="2" class="px-4 py-1 text-right text-xs font-semibold uppercase text-slate-500 md:hidden">IVA {{ $workOrder->tax_percentage }}%</td>
                        <td class="px-4 py-1 text-right font-semibold text-slate-700">{{ col_money($workOrder->tax_amount) }}</td>
                        <td colspan="{{ $can_manage ? 4 : 2 }}"></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="hidden px-4 py-2 text-right text-sm font-bold uppercase text-slate-900 md:table-cell">Total</td>
                        <td colspan="2" class="px-4 py-2 text-right text-sm font-bold uppercase text-slate-900 md:hidden">Total</td>
                        <td class="px-4 py-2 text-right text-base font-bold text-indigo-700">{{ col_money($workOrder->total) }}</td>
                        <td colspan="{{ $can_manage ? 4 : 2 }}"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </section>
        </div>

        <div class="space-y-4">
            @if($can_change_status)
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <h3 class="font-semibold text-slate-900">Estado de la OT</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        @if($status_change_disabled)
                            Esta OT está finalizada o cancelada y ya no admite cambios de estado.
                        @else
                            Actualiza el seguimiento de la orden de trabajo.
                        @endif
                    </p>
                </div>
                @if($status_change_disabled)
                    <div class="space-y-3 p-4 sm:p-5">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                            <input type="text" disabled value="{{ $workOrder->status_label }}"
                                class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 opacity-70">
                        </div>
                        @if($workOrder->finalized_at)
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Finalizada el</label>
                                <input type="text" disabled value="{{ $workOrder->finalized_at->format('d/m/Y H:i') }}"
                                    class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 opacity-70">
                            </div>
                        @endif
                        @if(! empty($status_comments_history))
                        <div class="space-y-2">
                            <p class="text-xs font-medium text-slate-500">Historial de comentarios</p>
                            <ul class="max-h-40 space-y-2 overflow-y-auto">
                                @foreach($status_comments_history as $entry)
                                <li class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                    <p class="font-medium text-slate-800">{{ $entry['comment'] }}</p>
                                    <p class="mt-1 text-[11px] text-slate-400">
                                        {{ $entry['status_label'] }}
                                        @if(! empty($entry['user_name'])) · {{ $entry['user_name'] }} @endif
                                        @if(! empty($entry['changed_at'])) · {{ $entry['changed_at'] }} @endif
                                    </p>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                @else
                <form wire:submit="updateStatus" class="space-y-4 p-4 sm:p-5">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                        <select wire:model.live="status"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('status') border-rose-400 bg-rose-50 @enderror">
                            @foreach($status_options as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">
                            Comentario
                            @if($show_cancel_comment_required)
                                <span class="text-rose-500">*</span>
                            @endif
                        </label>
                        <textarea wire:model="status_comment" rows="3"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('status_comment') border-rose-400 bg-rose-50 @enderror"
                            placeholder="{{ $status_comment_placeholder }}"></textarea>
                        @error('status_comment') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    @if(! empty($status_comments_history))
                    <div class="space-y-2">
                        <p class="text-xs font-medium text-slate-500">Historial de comentarios</p>
                        <ul class="max-h-40 space-y-2 overflow-y-auto">
                            @foreach($status_comments_history as $entry)
                            <li class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                <p class="font-medium text-slate-800">{{ $entry['comment'] }}</p>
                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{ $entry['status_label'] }}
                                    @if(! empty($entry['user_name'])) · {{ $entry['user_name'] }} @endif
                                    @if(! empty($entry['changed_at'])) · {{ $entry['changed_at'] }} @endif
                                </p>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                        <span wire:loading.remove wire:target="updateStatus">Guardar estado</span>
                        <span wire:loading wire:target="updateStatus">Guardando…</span>
                    </button>
                </form>
                @endif
            </section>
            @endif
        </div>
    </div>

    @if($showItemModal)
    <x-ui.modal centered maxWidth="xl">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeItemModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">{{ $editing_item_id ? 'Editar ítem' : 'Agregar ítem' }}</h3>
            <button type="button" wire:click="closeItemModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo de producto</label>
                    <select wire:model.live="product_type_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                        <option value="">Seleccionar…</option>
                        @foreach($product_types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Producto</label>
                    <select wire:model.live="product_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                        <option value="">Seleccionar…</option>
                        @foreach($catalog_products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ col_money($product->sale_price) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Descripción <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="item_description" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('item_description') border-rose-400 bg-rose-50 @enderror">
                    @error('item_description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Cantidad</label>
                    <input type="number" wire:model="item_quantity" min="0.01" step="0.01" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Precio unitario</label>
                    <input type="number" wire:model="item_unit_price" min="0" step="100" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Descuento (%)</label>
                    <input type="number" wire:model="item_discount" min="0" max="100" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Notas del técnico</label>
                    <input type="text" wire:model="item_notes" placeholder="Observaciones internas…" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
            </div>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <button type="button" wire:click="closeItemModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
            <button type="button" wire:click="saveItem" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">Guardar</button>
        </div>
    </x-ui.modal>
    @endif

    @if($showDocumentModal)
    <x-ui.modal centered maxWidth="md">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeDocumentModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">Documento asociado</h3>
            <button type="button" wire:click="closeDocumentModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="saveDocumentClient" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                @if(! empty($workOrder->document_client))
                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Registrados en esta OT</p>
                    <ul class="space-y-1.5">
                        @foreach($workOrder->document_client as $label => $saved_value)
                        <li>
                            <button type="button" wire:click="loadDocumentClient({{ json_encode($label) }})"
                                class="flex w-full items-start justify-between gap-3 rounded-lg px-2.5 py-2 text-left text-sm transition {{ $selected_document_label === $label ? 'bg-indigo-50 ring-1 ring-indigo-200' : 'hover:bg-white' }}">
                                <span class="font-medium text-slate-800">
                                    {{ $associated_documents->firstWhere('label', $label)?->value ?? $label }}
                                </span>
                                <span class="shrink-0 font-mono text-xs text-slate-600">{{ $saved_value }}</span>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Documento <span class="text-rose-500">*</span></label>
                    <select wire:model="selected_document_label"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('selected_document_label') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar…</option>
                        @forelse($associated_documents as $doc)
                        <option value="{{ $doc->label }}">{{ $doc->value }}</option>
                        @empty
                        <option value="" disabled>No hay documentos configurados</option>
                        @endforelse
                    </select>
                    @error('selected_document_label') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Valor <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="document_input_value" placeholder="Ej. número o referencia del documento"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('document_input_value') border-rose-400 bg-rose-50 @enderror">
                    @error('document_input_value') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closeDocumentModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                    <span wire:loading.remove wire:target="saveDocumentClient">Guardar</span>
                    <span wire:loading wire:target="saveDocumentClient">Guardando...</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
