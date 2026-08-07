<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.work-orders.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Órdenes de Trabajo</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $is_editing ? ($reference ?? 'Editar') : 'Nueva OT' }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                    {{ $is_editing ? 'OT ' . ($reference ?? '') : 'Nueva orden de trabajo' }}
                </h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Puedes crear la OT de forma directa o enlazarla a una cotización aceptada (solo las disponibles sin OT).
                </p>
            </div>
            @if($is_editing)
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.workshop.work-orders.show', $form->work_order_id) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Ver detalle</a>
                <a href="{{ route('admin.workshop.work-orders.print', $form->work_order_id) }}" target="_blank" class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Imprimir / PDF</a>
                @can('workshop.work-orders.delete')
                @if($can_delete)
                <button type="button" wire:click="deleteWorkOrder" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">Eliminar</button>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </header>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                        <h2 class="font-semibold text-slate-800">Datos de la OT</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-6">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">
                                Cotización aceptada
                                @unless($quotation_locked)
                                <span class="font-normal text-slate-400">(opcional)</span>
                                @endunless
                            </label>
                            <select wire:model.live="form.quotation_id" @disabled($quotation_locked)
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm disabled:opacity-60 @error('form.quotation_id') border-rose-400 bg-rose-50 @enderror">
                                <option value="">Sin cotización (OT directa)</option>
                                @foreach($accepted_quotations as $quotation)
                                <option value="{{ $quotation->id }}">
                                    {{ $quotation->reference }} — {{ $quotation->client?->name }} / {{ $quotation->equipment?->plate }}
                                </option>
                                @endforeach
                            </select>
                            @error('form.quotation_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">
                                @if($quotation_locked)
                                La OT se está creando desde esta cotización; no se puede cambiar.
                                @else
                                Solo aparecen cotizaciones en estado aceptada y sin otra OT asociada.
                                @endif
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Cliente <span class="text-rose-500">*</span></label>
                            <select wire:model.live="form.client_id" @disabled($from_quotation) class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm disabled:opacity-60 @error('form.client_id') border-rose-400 bg-rose-50 @enderror">
                                <option value="">Seleccionar cliente</option>
                                @foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach
                            </select>
                            @error('form.client_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Equipo <span class="text-rose-500">*</span></label>
                            <select wire:model="form.equipment_id" @disabled($from_quotation || ! $form->client_id) class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm disabled:opacity-60 @error('form.equipment_id') border-rose-400 bg-rose-50 @enderror">
                                <option value="">{{ $form->client_id ? 'Seleccionar equipo' : 'Primero selecciona un cliente' }}</option>
                                @foreach($equipment_for_client as $equipment)
                                <option value="{{ $equipment->id }}">{{ $equipment->select_label ?? ($equipment->plate . ' — ' . $equipment->name) }}</option>
                                @endforeach
                            </select>
                            @error('form.equipment_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Entrega estimada</label>
                            <input type="date" wire:model="form.estimated_delivery" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.estimated_delivery') border-rose-400 bg-rose-50 @enderror">
                            @error('form.estimated_delivery') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">IVA (%) <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model.live="form.tax_percentage" min="0" max="100" step="0.5" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.tax_percentage') border-rose-400 bg-rose-50 @enderror">
                            @error('form.tax_percentage') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Anticipo (%) <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model.live="form.advance_percentage" min="0" max="100" step="0.5"
                                @disabled(! $is_editing && $from_quotation)
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-60 @error('form.advance_percentage') border-rose-400 bg-rose-50 @enderror">
                            @error('form.advance_percentage') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">
                                Valor calculado:
                                <span class="font-medium text-slate-700">{{ col_money($preview_advance_amount) }}</span>
                                — solo define el monto acordado; los abonos se registran en Gestión de anticipo
                                @if(! $is_editing && $from_quotation)
                                <span class="text-slate-400">(heredado de la cotización)</span>
                                @endif
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Diagnóstico</label>
                            <textarea wire:model="form.diagnosis" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm"></textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Notas internas</label>
                            <textarea wire:model="form.notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm"></textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Observaciones</label>
                            <textarea wire:model="form.observations" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm"></textarea>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                    <div class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <h2 class="font-semibold text-slate-800">Ítems</h2>
                        <button type="button" wire:click="addItem" class="btn btn-primary btn-sm w-full justify-center sm:w-auto">+ Agregar ítem</button>
                    </div>
                    <div class="space-y-4 p-4 sm:p-6">
                        @forelse($items as $index => $row)
                        <div wire:key="wo-item-{{ $row['uid'] ?? $index }}" class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ítem {{ $index + 1 }}</p>
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-xs font-medium text-rose-600 hover:text-rose-700">Quitar</button>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Tipo</label>
                                    <select wire:model.live="items.{{ $index }}.product_type_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                        <option value="">—</option>
                                        @foreach($product_types as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Catálogo</label>
                                    <select wire:model.live="items.{{ $index }}.product_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" @disabled(empty($row['product_type_id']))>
                                        <option value="">— Manual —</option>
                                        @foreach($catalog_products->where('product_type_id', (int) ($row['product_type_id'] ?? 0)) as $ci)
                                        <option value="{{ $ci->id }}">{{ $ci->name }} ({{ col_money($ci->sale_price) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Descripción <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="items.{{ $index }}.description" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm @error('items.'.$index.'.description') border-rose-400 @enderror">
                                    @error('items.'.$index.'.description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Cantidad</label>
                                    <input type="number" wire:model.live="items.{{ $index }}.quantity" min="0.01" step="0.01" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Precio unitario</label>
                                    <input type="number" wire:model.live="items.{{ $index }}.unit_price" min="0" step="100" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Descuento (%)</label>
                                    <input type="number" wire:model.live="items.{{ $index }}.discount_percentage" min="0" max="100" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                                @php
                                $line = (float)($row['quantity'] ?? 0) * (float)($row['unit_price'] ?? 0) * (1 - (float)($row['discount_percentage'] ?? 0) / 100);
                                @endphp
                                <div class="flex items-end">
                                    <p class="w-full rounded-xl bg-indigo-50 px-3 py-2 text-right text-sm font-semibold text-indigo-700">{{ col_money($line) }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="py-6 text-center text-sm text-slate-400">Sin ítems. Usa «Agregar ítem» o selecciona una cotización aceptada.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div>
                <section class="sticky top-4 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035]">
                    <h3 class="font-semibold text-slate-900">Resumen</h3>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <dt>Subtotal</dt>
                            <dd class="tabular-nums font-medium text-slate-900">{{ col_money($preview_subtotal) }}</dd>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <dt>Anticipo ({{ $form->advance_percentage }}%)</dt>
                            <dd class="tabular-nums font-medium text-amber-700">{{ col_money($preview_advance_amount) }}</dd>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <dt>IVA ({{ $form->tax_percentage }}%)</dt>
                            <dd class="tabular-nums font-medium text-slate-900">{{ col_money($preview_tax) }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-semibold text-slate-900">
                            <dt>Total</dt>
                            <dd class="tabular-nums text-indigo-700">{{ col_money($preview_total) }}</dd>
                        </div>
                    </dl>
                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-col">
                        <a href="{{ route('admin.workshop.work-orders.index') }}" wire:navigate class="btn btn-outline-secondary w-full justify-center">Cancelar</a>
                        <button type="submit" wire:loading.attr="disabled" class="btn btn-primary w-full justify-center disabled:opacity-60">
                            {{ $is_editing ? 'Guardar cambios' : 'Crear OT' }}
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </form>
</div>
