<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.work-orders.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Órdenes de Trabajo</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $is_editing ? ($reference ?? 'Editar') : 'Nueva OT' }}</span>
    </nav>

    <header class="mb-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ $is_editing ? 'OT ' . ($reference ?? '') : 'Nueva orden de trabajo' }}
                </h1>
                <p class="mt-1 max-w-xl text-sm text-slate-600">
                    Completa la OT por pasos. El resumen permanece visible.
                </p>
            </div>
            @if($is_editing)
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.workshop.work-orders.show', $form->work_order_id) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Ver detalle</a>
                <a href="{{ route('admin.workshop.work-orders.print', $form->work_order_id) }}" target="_blank" class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Imprimir / PDF</a>
                @if($can_create_remission)
                <a href="{{ route('admin.workshop.remissions.form', ['work_order' => $form->work_order_id]) }}" wire:navigate
                    class="btn btn-success btn-sm flex-1 sm:flex-none justify-center">
                    Crear remisión
                </a>
                @elseif($linked_remission)
                <a href="{{ route('admin.workshop.remissions.show', $linked_remission) }}" wire:navigate
                    class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">
                    Ver remisión
                </a>
                @endif
                @can('workshop.work-orders.delete')
                @if($can_delete)
                <button type="button" wire:click="deleteWorkOrder" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">Eliminar</button>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </header>

    <form @if($step === $total_steps) wire:submit="save" @else wire:submit.prevent="nextStep" @endif class="space-y-4">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-3 shadow-sm ring-1 ring-slate-900/[0.035] sm:p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-6">
                <div class="flex shrink-0 items-center gap-3">
                    <div class="relative h-14 w-14" role="img" aria-label="Progreso {{ $progress }} por ciento">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 72 72" aria-hidden="true">
                            <circle cx="36" cy="36" r="30" fill="none" class="stroke-slate-100" stroke-width="6"></circle>
                            <circle cx="36" cy="36" r="30" fill="none" class="stroke-indigo-600" stroke-width="6" stroke-linecap="round" stroke-dasharray="{{ $progress_circumference }}" stroke-dashoffset="{{ $progress_offset }}"></circle>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-sm font-bold tabular-nums text-indigo-600">{{ $progress }}%</span>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Progreso</p>
                        <p class="text-sm font-semibold text-slate-800">Paso {{ $step }} de {{ $total_steps }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $steps[$step]['title'] }}</p>
                    </div>
                </div>

                <ol class="flex min-w-0 flex-1 items-start">
                    @foreach($steps as $number => $meta)
                    @php
                        $is_done = $step > $number;
                        $is_current = $step === $number;
                        $is_last = $number === $total_steps;
                    @endphp
                    <li class="flex {{ $is_last ? 'shrink-0' : 'min-w-0 flex-1' }} items-start">
                        <button type="button" wire:click="goToStep({{ $number }})" class="flex shrink-0 flex-col items-center gap-2">
                            <span class="relative flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold transition {{ $is_done ? 'bg-indigo-600 text-white' : ($is_current ? 'bg-indigo-600 text-white ring-[3px] ring-indigo-200' : 'border-2 border-indigo-200 bg-white text-indigo-400') }}">
                                @if($is_done)
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @else
                                {{ $number }}
                                @endif
                            </span>
                            <span class="max-w-[6.5rem] text-center text-[11px] font-medium leading-tight sm:text-xs {{ $is_current ? 'text-indigo-700' : ($is_done ? 'text-slate-600' : 'text-slate-400') }}">
                                {{ $meta['title'] }}
                            </span>
                        </button>
                        @if(! $is_last)
                        <div class="mt-[1.125rem] h-0.5 min-w-4 flex-1 {{ $is_done ? 'bg-indigo-600' : 'bg-slate-200' }}" aria-hidden="true"></div>
                        @endif
                    </li>
                    @endforeach
                </ol>

                <div class="flex w-full shrink-0 flex-wrap gap-2 lg:w-auto lg:justify-end">
                    <a href="{{ $is_editing ? route('admin.workshop.work-orders.show', $form->work_order_id) : route('admin.workshop.work-orders.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Cancelar</a>
                    @if($step > 1)
                    <button type="button" wire:click="previousStep" class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Anterior</button>
                    @endif
                    @if($step < $total_steps)
                    <button type="button" wire:click="nextStep" wire:loading.attr="disabled" class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">Siguiente</button>
                    @else
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">
                        <span wire:loading.remove wire:target="save">{{ $is_editing ? 'Guardar' : 'Crear' }}</span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </button>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="order-2 space-y-4 lg:order-1 lg:col-span-2">
                @if($step === 1)
                <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 1 de {{ $total_steps }}</p>
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
                                    {{ $quotation->reference }} — {{ $quotation->client?->name }}
                                    @if($quotation->equipments->isNotEmpty())
                                        / {{ $quotation->equipments->pluck('plate')->filter()->join(', ') }}
                                    @endif
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
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Equipos <span class="text-rose-500">*</span></label>
                            @if(! $form->client_id)
                                <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-400">Primero selecciona un cliente</p>
                            @elseif($equipment_for_client->isEmpty())
                                <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-400">Este cliente no tiene equipos activos</p>
                            @else
                                <div class="max-h-48 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 @error('form.equipment_ids') border-rose-400 bg-rose-50 @enderror @error('form.equipment_ids.*') border-rose-400 bg-rose-50 @enderror {{ $from_quotation ? 'opacity-60' : '' }}">
                                    @foreach($equipment_for_client as $equipment)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-lg px-2 py-1.5 transition hover:bg-white {{ $from_quotation ? 'cursor-not-allowed' : '' }}">
                                            <input type="checkbox"
                                                wire:model.live="form.equipment_ids"
                                                value="{{ $equipment->id }}"
                                                @disabled($from_quotation)
                                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 disabled:cursor-not-allowed">
                                            <span class="text-sm text-slate-800">{{ $equipment->select_label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            @error('form.equipment_ids') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            @error('form.equipment_ids.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">
                                @if($from_quotation)
                                    Los equipos se heredan de la cotización.
                                @else
                                    Puedes seleccionar varios equipos. Luego asigna cada ítem a uno de ellos.
                                @endif
                            </p>
                        </div>
                    </div>
                </section>
                @endif

                @if($step === 2)
                <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 2 de {{ $total_steps }}</p>
                        <h2 class="font-semibold text-slate-800">Condiciones</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-6">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Entrega estimada</label>
                            <input type="date" wire:model="form.estimated_delivery" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.estimated_delivery') border-rose-400 bg-rose-50 @enderror">
                            @error('form.estimated_delivery') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">IVA (%)</label>
                            <input type="number" wire:model.live="form.tax_percentage" min="0" max="100" step="0.01" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.tax_percentage') border-rose-400 bg-rose-50 @enderror">
                            @error('form.tax_percentage') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Anticipo (%)</label>
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
                @endif

                @if($step === 3)
                <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                    <div class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 3 de {{ $total_steps }}</p>
                            <h2 class="font-semibold text-slate-800">Ítems</h2>
                        </div>
                        <button type="button" wire:click="addItem" class="btn btn-primary btn-sm w-full justify-center sm:w-auto">+ Agregar ítem</button>
                    </div>
                    <div class="space-y-4 p-4 sm:p-6">
                        @error('items') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        @forelse($items as $index => $row)
                        <div wire:key="wo-item-{{ $row['uid'] ?? $index }}" class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ítem {{ $index + 1 }}</p>
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-xs font-medium text-rose-600 hover:text-rose-700">Quitar</button>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Equipo <span class="text-rose-500">*</span></label>
                                    <select wire:model="items.{{ $index }}.equipment_id"
                                        @disabled($selected_equipments->isEmpty())
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm disabled:opacity-60 @error('items.'.$index.'.equipment_id') border-rose-400 @enderror">
                                        <option value="">{{ $selected_equipments->isEmpty() ? 'Selecciona equipos arriba' : 'Asignar a equipo' }}</option>
                                        @foreach($selected_equipments as $equipment)
                                            <option value="{{ $equipment->id }}">{{ $equipment->select_label }}</option>
                                        @endforeach
                                    </select>
                                    @error('items.'.$index.'.equipment_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
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
                                    <input type="number" wire:model.live="items.{{ $index }}.unit_price" min="0" step="0.01" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Descuento (%)</label>
                                    <input type="number" wire:model.live="items.{{ $index }}.discount_percentage" min="0" max="100" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                                <div class="flex items-end">
                                    <p class="w-full rounded-xl bg-indigo-50 px-3 py-2 text-right text-sm font-semibold text-indigo-700">{{ col_money($item_line_totals[$index] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="py-6 text-center text-sm text-slate-400">Sin ítems. Usa «Agregar ítem» o selecciona una cotización aceptada.</p>
                        @endforelse
                    </div>
                </section>
                @endif
            </div>

            <div class="order-1 lg:order-2">
                <section class="sticky top-2 z-10 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035] lg:top-4">
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
                </section>
            </div>
        </div>
    </form>
</div>
