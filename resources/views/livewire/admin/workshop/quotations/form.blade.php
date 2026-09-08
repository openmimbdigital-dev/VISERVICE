<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.quotations.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Cotizaciones</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $is_editing ? ($reference ?? 'Editar') : 'Nueva cotización' }}</span>
    </nav>

    <header class="mb-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ $is_editing ? 'Cotización ' . ($reference ?? '') : 'Nueva cotización' }}
                </h1>
                @if($is_editing && $status_label)
                <p class="mt-1">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $status_badge_class }}">{{ $status_label }}</span>
                </p>
                @else
                <p class="mt-1 max-w-xl text-sm text-slate-600">Completa la cotización por pasos. El resumen permanece visible.</p>
                @endif
            </div>
            @if($is_editing)
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.workshop.quotations.print', $form->quotation_id) }}" target="_blank" class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Imprimir / PDF</a>
                @if($can_create_ot)
                <a href="{{ route('admin.workshop.work-orders.form', ['quotation' => $form->quotation_id]) }}" wire:navigate
                    class="btn btn-success btn-sm flex-1 sm:flex-none justify-center">
                    Crear OT
                </a>
                @elseif($linked_work_order_id)
                <a href="{{ route('admin.workshop.work-orders.show', $linked_work_order_id) }}" wire:navigate
                    class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">
                    Ver OT {{ $linked_work_order_reference }}
                </a>
                @endif
                @can('workshop.quotations.delete')
                @if($can_delete)
                <button type="button" wire:click="deleteQuotation" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">Eliminar</button>
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
                <a href="{{ $is_editing ? route('admin.workshop.quotations.show', $form->quotation_id) : route('admin.workshop.quotations.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Cancelar</a>
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
                        <h2 class="font-semibold text-slate-800">Datos de la cotización</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-6">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Cliente <span class="text-rose-500">*</span></label>
                            <livewire:ui.searchable-select
                                wire:model.live="form.client_id"
                                model-class="App\Models\Client"
                                :search-by="['document_number']"
                                label-field="name"
                                :filters="['status' => true]"
                                placeholder="Seleccionar cliente"
                                search-placeholder="Buscar por documento..."
                                :invalid="$errors->has('form.client_id')"
                                :key="'quotation-client-select'"
                            />
                            @error('form.client_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Equipos <span class="text-rose-500">*</span></label>
                            @if(! $form->client_id)
                                <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-400">Primero selecciona un cliente</p>
                            @elseif($equipment_for_client->isEmpty())
                                <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-400">Este cliente no tiene equipos activos</p>
                            @else
                                <div class="max-h-48 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 @error('form.equipment_ids') border-rose-400 bg-rose-50 @enderror @error('form.equipment_ids.*') border-rose-400 bg-rose-50 @enderror">
                                    @foreach($equipment_for_client as $equipment)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-lg px-2 py-1.5 transition hover:bg-white">
                                            <input type="checkbox"
                                                wire:model.live="form.equipment_ids"
                                                value="{{ $equipment->id }}"
                                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-slate-800">{{ $equipment->select_label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            @error('form.equipment_ids') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            @error('form.equipment_ids.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">Puedes seleccionar varios equipos. Luego asigna cada ítem a uno de ellos.</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Horas al ingreso</label>
                            <input type="time" wire:model="form.hours_entry" step="60"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.hours_entry') border-rose-400 bg-rose-50 @enderror">
                            @error('form.hours_entry') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Notas internas</label>
                            <textarea wire:model="form.notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm"></textarea>
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
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo de servicio</label>
                            <livewire:ui.searchable-select
                                wire:model="form.quotation_service_type_id"
                                model-class="App\Models\QuotationServiceType"
                                :search-by="['name']"
                                label-field="name"
                                :filters="['active' => true]"
                                placeholder="Seleccionar tipo de servicio"
                                search-placeholder="Buscar tipo de servicio..."
                                :invalid="false"
                                :key="'quotation-service-type-select'"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Vigencia (días) <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model="form.validity_days" min="1" max="365" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.validity_days') border-rose-400 @enderror">
                            @error('form.validity_days') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Forma de pago</label>
                            <livewire:ui.searchable-select
                                wire:model="form.business_payment_method_id"
                                model-class="App\Models\BusinessPaymentMethod"
                                :search-by="['name']"
                                label-field="name"
                                order-by="sort_order"
                                :filters="['active' => true]"
                                placeholder="Seleccionar forma de pago"
                                search-placeholder="Buscar forma de pago..."
                                :invalid="false"
                                :key="'quotation-payment-method-select'"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Cuenta bancaria</label>
                            <livewire:ui.searchable-select
                                wire:model="form.business_bank_account_id"
                                model-class="App\Models\BusinessBankAccount"
                                :search-by="['bank_name', 'account_number']"
                                label-field="select_label"
                                order-by="bank_name"
                                :filters="['active' => true, 'business_id' => $form->resolvedBusinessId()]"
                                placeholder="Seleccionar cuenta bancaria"
                                search-placeholder="Buscar por banco o número..."
                                :invalid="false"
                                :key="'quotation-bank-account-select-'.$form->resolvedBusinessId()"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Tiempo de ejecución</label>
                            <input type="text" wire:model="form.execution_time" placeholder="Ej. 2 días hábiles" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Impuestos</label>
                            <livewire:ui.searchable-select
                                wire:model.live="pending_custom_tax_id"
                                model-class="App\Models\CustomTax"
                                :search-by="['name']"
                                label-field="select_label"
                                order-by="name"
                                :filters="['active' => true, 'business_id' => $form->resolvedBusinessId(), 'exclude_ids' => $form->custom_tax_ids]"
                                placeholder="Agregar impuesto"
                                search-placeholder="Buscar impuesto..."
                                :invalid="$errors->has('form.custom_tax_ids') || $errors->has('form.custom_tax_ids.*')"
                                :key="'quotation-custom-tax-select-'.$form->resolvedBusinessId().'-'.implode('-', $form->custom_tax_ids)"
                            />
                            @error('form.custom_tax_ids') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            @error('form.custom_tax_ids.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <div class="mt-2 space-y-2">
                                @forelse($applied_tax_lines as $tax)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-800">{{ $tax['name'] }} ({{ $tax['percentage_label'] }}%)</p>
                                        <p class="text-xs text-slate-500">{{ col_money($tax['amount']) }}</p>
                                    </div>
                                    <button type="button" wire:click="removeCustomTax({{ $tax['id'] }})" class="shrink-0 rounded-lg p-1 text-slate-400 transition hover:bg-white hover:text-rose-600" title="Quitar impuesto">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                @empty
                                <p class="text-xs text-slate-400">Sin impuestos. Busca y selecciona uno o más.</p>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Anticipo (%)</label>
                            <input type="number" wire:model.live="form.advance_percentage" min="0" max="100" step="0.5" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.advance_percentage') border-rose-400 bg-rose-50 @enderror">
                            @error('form.advance_percentage') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">
                                Valor calculado: <span class="font-medium text-slate-700">{{ col_money($preview_advance_amount) }}</span>
                                — solo define el monto acordado; los abonos se gestionan en Gestión de anticipo (tras crear la OT)
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Diagnóstico</label>
                            <textarea wire:model="form.diagnosis" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm"></textarea>
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
                        @php $item_product = $catalog_by_id->get((int) ($row['product_id'] ?? 0)); @endphp
                        <div wire:key="item-row-{{ $index }}" class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
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
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Tipo <span class="text-rose-500">*</span></label>
                                    <select wire:model.live="items.{{ $index }}.product_type_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm @error('items.'.$index.'.product_type_id') border-rose-400 @enderror">
                                        <option value="">—</option>
                                        @foreach($product_types as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
                                    </select>
                                    @error('items.'.$index.'.product_type_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Producto <span class="text-rose-500">*</span></label>
                                    <select wire:model.live="items.{{ $index }}.product_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm @error('items.'.$index.'.product_id') border-rose-400 @enderror" @disabled(empty($row['product_type_id']))>
                                        <option value="">Seleccionar producto</option>
                                        @foreach($catalog_products->where('product_type_id', (int) ($row['product_type_id'] ?? 0)) as $ci)
                                        <option value="{{ $ci->id }}">{{ $ci->name }} ({{ col_money($ci->sale_price) }})</option>
                                        @endforeach
                                    </select>
                                    @error('items.'.$index.'.product_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Descripción</label>
                                    <p class="flex min-h-[42px] items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                                        {{ $item_product?->name ?? 'Selecciona un producto' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Cantidad <span class="text-rose-500">*</span></label>
                                    <input type="number" wire:model.live="items.{{ $index }}.quantity" min="0.01" step="0.01" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm @error('items.'.$index.'.quantity') border-rose-400 @enderror">
                                    @error('items.'.$index.'.quantity') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Precio unitario</label>
                                    <p class="flex min-h-[42px] items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm tabular-nums font-medium text-slate-800">
                                        @if($item_product)
                                            {{ col_money($item_product->sale_price) }}
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Descuento</label>
                                    <div class="flex min-h-[42px] items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                        <label class="flex shrink-0 items-center gap-2 {{ $item_product?->hasDiscount() ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}">
                                            <input type="checkbox"
                                                wire:model.live="items.{{ $index }}.apply_discount"
                                                @disabled(! $item_product?->hasDiscount())
                                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 disabled:cursor-not-allowed">
                                            <span class="text-xs font-medium text-slate-700">Aplicar</span>
                                        </label>
                                        <span class="min-w-0 text-sm text-slate-800">
                                            @if($item_product?->hasDiscount())
                                                {{ $item_product->discountLabel() }}
                                            @else
                                                Sin descuento
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-end">
                                    <p class="w-full rounded-xl bg-indigo-50 px-3 py-2 text-right text-sm font-semibold text-indigo-700">{{ col_money($item_line_totals[$index] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="py-6 text-center text-sm text-slate-400">Sin ítems. Usa «Agregar ítem» para incluir productos o servicios.</p>
                        @endforelse
                    </div>
                </section>
                @endif
            </div>

            <div class="order-1 lg:order-2">
                <section class="sticky top-2 z-10 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035] lg:top-4">
                    <h3 class="font-semibold text-slate-900">Resumen</h3>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-xs text-slate-500"><dt>Mano de obra</dt><dd>{{ col_money($category_subtotals['mano_obra']) }}</dd></div>
                        <div class="flex justify-between text-xs text-slate-500"><dt>Repuestos</dt><dd>{{ col_money($category_subtotals['repuestos']) }}</dd></div>
                        <div class="flex justify-between text-xs text-slate-500"><dt>Lubricantes</dt><dd>{{ col_money($category_subtotals['lubricantes']) }}</dd></div>
                        <div class="flex justify-between text-xs text-slate-500"><dt>Otros</dt><dd>{{ col_money($category_subtotals['otros']) }}</dd></div>
                        <div class="flex justify-between border-t border-slate-100 pt-2"><dt class="text-slate-500">Subtotal</dt><dd class="font-medium">{{ col_money($preview_subtotal) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Anticipo ({{ $form->advance_percentage }}%)</dt><dd class="font-medium text-amber-700">{{ col_money($preview_advance_amount) }}</dd></div>
                        @forelse($applied_tax_lines as $tax)
                        <div class="flex justify-between"><dt class="text-slate-500">{{ $tax['name'] }} ({{ $tax['percentage_label'] }}%)</dt><dd class="font-medium">{{ col_money($tax['amount']) }}</dd></div>
                        @empty
                        <div class="flex justify-between"><dt class="text-slate-500">Impuestos</dt><dd class="font-medium">{{ col_money(0) }}</dd></div>
                        @endforelse
                        <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-bold"><dt>Total</dt><dd class="text-indigo-700">{{ col_money($preview_total) }}</dd></div>
                    </dl>
                </section>
            </div>
        </div>
    </form>
</div>
