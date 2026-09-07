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
                    <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 3 de {{ $total_steps }}</p>
                                <h2 class="font-semibold text-slate-800">Catálogo de productos</h2>
                            </div>
                            <button type="button" wire:click="addItem" class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">+ Ítem manual / servicio</button>
                        </div>

                        @if($selected_equipments->count() > 1)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Equipo activo para nuevos productos <span class="text-rose-500">*</span></label>
                            <select wire:model.live="active_equipment_id" class="w-full max-w-sm rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm @error('active_equipment_id') border-rose-400 @enderror">
                                <option value="">Selecciona un equipo</option>
                                @foreach($selected_equipments as $equipment)
                                <option value="{{ $equipment->id }}">{{ $equipment->select_label }}</option>
                                @endforeach
                            </select>
                            @error('active_equipment_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">Los productos que agregues del catálogo se asignarán a este equipo.</p>
                        </div>
                        @elseif($selected_equipments->isEmpty())
                        <p class="rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-sm text-amber-800">Selecciona el cliente y al menos un equipo en el paso 1 para poder agregar productos del catálogo.</p>
                        @endif
                    </div>

                    <div class="border-b border-slate-100 p-4 sm:p-5">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="search" wire:model.live.debounce.300ms="catalog_search" placeholder="Buscar producto por nombre o SKU..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3.5 text-sm">
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" wire:click="$set('catalog_type_filter', null)"
                                class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ ! $catalog_type_filter ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                Todos
                            </button>
                            @foreach($product_types as $type)
                            <button type="button" wire:click="$set('catalog_type_filter', {{ $type->id }})"
                                class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ (int) $catalog_type_filter === $type->id ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ $type->name }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div
                        x-on:scroll.throttle.200ms="if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 150 && @js($catalog_has_more)) { $wire.loadMoreCatalogProducts() }"
                        wire:loading.class="opacity-50" wire:target="catalog_search,catalog_type_filter"
                        class="max-h-[34rem] overflow-y-auto p-4 transition sm:p-5">
                        @if($catalog_products->isEmpty())
                        <p class="py-10 text-center text-sm text-slate-400">No hay productos que coincidan con la búsqueda.</p>
                        @else
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                            @foreach($catalog_products as $product)
                            @php $in_cart = (float) ($cart_quantities[$product->id] ?? 0); @endphp
                            <div wire:key="catalog-product-{{ $product->id }}" class="group relative flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white transition hover:border-indigo-300 hover:shadow-md">
                                @if($product->product_type)
                                <span class="absolute left-2 top-2 z-10 inline-flex items-center rounded-full bg-slate-900/70 px-2 py-0.5 text-[10px] font-semibold text-white shadow backdrop-blur-sm">
                                    {{ $product->product_type->name }}
                                </span>
                                @endif
                                @if($product->hasDiscount())
                                <span class="absolute {{ $in_cart > 0 ? 'right-2 top-9' : 'right-2 top-2' }} z-10 inline-flex items-center rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white shadow">
                                    −{{ $product->discountLabel() }}
                                </span>
                                @endif
                                @if($in_cart > 0)
                                <span class="absolute right-2 top-2 z-10 inline-flex items-center rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white shadow">
                                    En OT: {{ rtrim(rtrim(number_format($in_cart, 2, '.', ''), '0'), '.') }}
                                </span>
                                @endif
                                <button type="button" wire:click="showProductPreview({{ $product->id }})" class="flex flex-1 flex-col text-left">
                                    <x-ui.product-image :product="$product" size="xl" class="border-b border-slate-100" />
                                    <div class="flex flex-1 flex-col gap-1 p-3">
                                        <p class="line-clamp-2 text-sm font-medium leading-snug text-slate-800" title="{{ $product->name }}">{{ $product->name }}</p>
                                        <p class="font-mono text-[11px] text-slate-400">{{ $product->sku }}</p>
                                        @if($product->hasDiscount())
                                        <div class="mt-auto">
                                            <p class="text-[11px] text-slate-400 line-through">{{ col_money($product->sale_price) }}</p>
                                            <p class="text-sm font-semibold text-emerald-700">{{ col_money($product->finalPrice()) }}</p>
                                        </div>
                                        @else
                                        <p class="mt-auto text-sm font-semibold text-indigo-700">{{ col_money($product->sale_price) }}</p>
                                        @endif
                                    </div>
                                </button>
                                <div class="flex items-center gap-1.5 border-t border-slate-100 p-2">
                                    <div class="flex shrink-0 items-center rounded-lg border border-slate-200">
                                        <button type="button" tabindex="-1"
                                            x-on:click="const input = $el.nextElementSibling; input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1); input.dispatchEvent(new Event('input'))"
                                            class="px-2 py-1.5 text-slate-500 hover:bg-slate-100">−</button>
                                        <input type="number" readonly wire:model="catalog_quantities.{{ $product->id }}" min="1" step="1"
                                            class="w-8 border-0 bg-transparent p-0 text-center text-xs focus:outline-none focus:ring-0">
                                        <button type="button" tabindex="-1"
                                            x-on:click="const input = $el.previousElementSibling; input.value = (parseInt(input.value, 10) || 1) + 1; input.dispatchEvent(new Event('input'))"
                                            class="px-2 py-1.5 text-slate-500 hover:bg-slate-100">+</button>
                                    </div>
                                    <button type="button" wire:click="addCatalogItem({{ $product->id }})" wire:loading.attr="disabled"
                                        class="btn btn-primary btn-sm min-w-0 flex-1 justify-center !px-2 !py-1.5 !text-xs">
                                        Agregar
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div wire:loading.flex wire:target="loadMoreCatalogProducts" class="hidden items-center justify-center gap-2 py-4 text-xs font-medium text-slate-500">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Cargando más productos...
                        </div>
                        @if(! $catalog_has_more && $catalog_products->count() > 20)
                        <p class="py-3 text-center text-xs text-slate-400">Has llegado al final del catálogo.</p>
                        @endif
                        @endif
                    </div>
                </section>

                <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
                        <h2 class="font-semibold text-slate-800">Ítems de la OT</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Ajusta la cantidad y el equipo asignado. El precio y el descuento los define el catálogo.</p>
                    </div>
                    <div class="space-y-3 p-4 sm:p-6">
                        @error('items') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        @forelse($items as $index => $row)
                        @php $cart_product = ! empty($row['product_id']) ? $cart_products->get((int) $row['product_id']) : null; @endphp
                        <div wire:key="wo-item-{{ $row['uid'] ?? $index }}" class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50/50 p-3 sm:flex-row sm:items-center">
                            @if($cart_product)
                            <div class="flex flex-1 items-center gap-3">
                                <x-ui.product-image :product="$cart_product" size="sm" />
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-800">{{ $row['description'] }}</p>
                                    <div class="mt-0.5 flex flex-wrap items-center gap-1">
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-600">Catálogo</span>
                                        @if((float) ($row['discount_percentage'] ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800"
                                            title="Descuento definido en el producto">
                                            −{{ rtrim(rtrim(number_format((float) $row['discount_percentage'], 2, '.', ''), '0'), '.') }}% descuento
                                            <span class="font-normal">({{ col_money($item_line_discounts[$index] ?? 0) }})</span>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="flex-1">
                                <label class="mb-1 block text-xs font-medium text-slate-700">Descripción <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="items.{{ $index }}.description" placeholder="Ej. Mano de obra, servicio adicional..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm @error('items.'.$index.'.description') border-rose-400 @enderror">
                                @error('items.'.$index.'.description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            @endif

                            <div class="grid grid-cols-2 gap-2 sm:flex sm:shrink-0 sm:items-end">
                                <div class="w-full sm:w-36">
                                    <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-500">Equipo</label>
                                    <select wire:model="items.{{ $index }}.equipment_id" @disabled($selected_equipments->isEmpty())
                                        class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs disabled:opacity-60 @error('items.'.$index.'.equipment_id') border-rose-400 @enderror">
                                        <option value="">Sin asignar</option>
                                        @foreach($selected_equipments as $equipment)
                                        <option value="{{ $equipment->id }}">{{ $equipment->select_label }}</option>
                                        @endforeach
                                    </select>
                                    @error('items.'.$index.'.equipment_id') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                {{-- Contador en vez de input abierto: la cantidad se sube y baja de a uno. --}}
                                <div class="w-full sm:w-24">
                                    <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-500">Cant.</label>
                                    <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white">
                                        <button type="button" tabindex="-1" wire:click="changeItemQuantity({{ $index }}, -1)" wire:loading.attr="disabled"
                                            class="px-2 py-1.5 text-slate-500 transition hover:bg-slate-100 disabled:opacity-40" title="Quitar uno">−</button>
                                        <span class="px-1 text-xs font-semibold tabular-nums text-slate-700">{{ rtrim(rtrim(number_format((float) ($row['quantity'] ?? 0), 2, '.', ''), '0'), '.') }}</span>
                                        <button type="button" tabindex="-1" wire:click="changeItemQuantity({{ $index }}, 1)" wire:loading.attr="disabled"
                                            class="px-2 py-1.5 text-slate-500 transition hover:bg-slate-100 disabled:opacity-40" title="Agregar uno">+</button>
                                    </div>
                                    @error('items.'.$index.'.quantity') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="w-full sm:w-28">
                                    <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-500">Precio</label>
                                    @if($cart_product)
                                    {{-- Precio bloqueado: lo define el catálogo, no la OT. --}}
                                    <div class="rounded-lg border border-slate-200 bg-slate-100 px-2 py-1.5 text-center text-xs text-slate-600"
                                        title="El precio viene del catálogo. Para cambiarlo, edita el producto.">
                                        @if((float) ($row['discount_percentage'] ?? 0) > 0)
                                        <span class="text-[10px] text-slate-400 line-through">{{ col_money($row['unit_price'] ?? 0) }}</span>
                                        <span class="ml-1 font-semibold text-emerald-700">{{ col_money((float) ($row['unit_price'] ?? 0) * (1 - (float) $row['discount_percentage'] / 100)) }}</span>
                                        @else
                                        <span class="font-medium">{{ col_money($row['unit_price'] ?? 0) }}</span>
                                        @endif
                                    </div>
                                    @else
                                    {{-- Un ítem manual (mano de obra, servicio) no tiene catálogo del cual tomar el precio. --}}
                                    <input type="number" wire:model.live="items.{{ $index }}.unit_price" min="0" step="0.01"
                                        class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                                    @error('items.'.$index.'.unit_price') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                                    @endif
                                </div>
                                <div class="w-full sm:w-24">
                                    <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-500">Total</label>
                                    <p class="rounded-lg bg-indigo-50 px-2 py-1.5 text-center text-xs font-semibold text-indigo-700">{{ col_money($item_line_totals[$index] ?? 0) }}</p>
                                </div>
                                <button type="button" wire:click="removeItem({{ $index }})" class="mb-0.5 shrink-0 rounded-lg p-1.5 text-rose-500 hover:bg-rose-50" title="Quitar">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        @empty
                        <p class="py-6 text-center text-sm text-slate-400">Sin ítems. Agrega productos del catálogo arriba o usa «Ítem manual / servicio».</p>
                        @endforelse
                    </div>
                </section>
                @endif
            </div>

            <div class="order-1 lg:order-2">
                <section class="sticky top-2 z-10 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035] lg:top-4">
                    <h3 class="font-semibold text-slate-900">Resumen</h3>
                    <dl class="mt-4 space-y-2 text-sm">
                        @if($items_discount_total > 0)
                        <div class="flex justify-between text-slate-500">
                            <dt class="text-xs">Precio de lista</dt>
                            <dd class="tabular-nums text-xs">{{ col_money($preview_subtotal + $items_discount_total) }}</dd>
                        </div>
                        <div class="flex justify-between text-amber-700">
                            <dt class="text-xs">Descuentos de productos</dt>
                            <dd class="tabular-nums text-xs font-medium">−{{ col_money($items_discount_total) }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between text-slate-600">
                            <dt>Subtotal</dt>
                            <dd class="tabular-nums font-medium text-slate-900">{{ col_money($preview_subtotal) }}</dd>
                        </div>
                        @if($applied_coupon)
                        <div class="flex justify-between text-emerald-700">
                            <dt>Cupón {{ $applied_coupon->code }}</dt>
                            <dd class="tabular-nums font-medium">−{{ col_money($coupon_discount) }}</dd>
                        </div>
                        @endif
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

                    @if($step === 3)
                    {{-- Descuento de toda la OT: solo entra por cupón, no a mano. --}}
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cupón de descuento</h4>

                        @if($applied_coupon)
                        <div class="mt-2 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2">
                            <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-semibold text-emerald-800">{{ $applied_coupon->code }}</p>
                                <p class="truncate text-[11px] text-emerald-700">
                                    {{ $applied_coupon->name ?: 'Descuento de '.$applied_coupon->discountLabel() }} · −{{ col_money($coupon_discount) }}
                                </p>
                            </div>
                            <button type="button" wire:click="removeCoupon" class="shrink-0 rounded-lg p-1 text-emerald-700 transition hover:bg-emerald-100" title="Quitar cupón">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        @else
                        <div class="mt-2 flex gap-2">
                            <input type="text" wire:model="coupon_input" wire:keydown.enter.prevent="applyCoupon" placeholder="Código"
                                class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs uppercase placeholder:normal-case @error('coupon_input') border-rose-400 @enderror">
                            <button type="button" wire:click="applyCoupon" wire:loading.attr="disabled" wire:target="applyCoupon"
                                class="btn btn-secondary btn-sm shrink-0 !px-3 !py-1.5 !text-xs">Aplicar</button>
                        </div>
                        @endif

                        @error('coupon_input') <p class="mt-1.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        @error('coupon_code') <p class="mt-1.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ítems agregados</h4>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">{{ count($items) }}</span>
                        </div>

                        @if(empty($items))
                        <p class="mt-2 text-xs text-slate-400">Aún no has agregado productos.</p>
                        @else
                        <div class="mt-2 max-h-64 space-y-1.5 overflow-y-auto pr-1">
                            @foreach($items as $index => $row)
                            @php $summary_product = ! empty($row['product_id']) ? $cart_products->get((int) $row['product_id']) : null; @endphp
                            <div wire:key="summary-item-{{ $row['uid'] ?? $index }}" class="flex items-center gap-2 rounded-lg bg-slate-50 px-2 py-1.5">
                                @if($summary_product)
                                <x-ui.product-image :product="$summary_product" size="sm" class="!h-8 !w-8" />
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-medium text-slate-700">{{ $row['description'] ?: 'Ítem' }}</p>
                                    <p class="flex items-center gap-1 text-[11px] text-slate-400">
                                        x{{ rtrim(rtrim(number_format((float) ($row['quantity'] ?? 0), 2, '.', ''), '0'), '.') }}
                                        @if((float) ($row['discount_percentage'] ?? 0) > 0)
                                        <span class="rounded bg-amber-100 px-1 font-semibold text-amber-800">−{{ rtrim(rtrim(number_format((float) $row['discount_percentage'], 2, '.', ''), '0'), '.') }}%</span>
                                        @endif
                                    </p>
                                </div>
                                <p class="shrink-0 text-xs font-semibold text-slate-700">{{ col_money($item_line_totals[$index] ?? 0) }}</p>
                                <button type="button" wire:click="removeItem({{ $index }})" class="shrink-0 rounded p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Quitar">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                </section>
            </div>
        </div>
    </form>

    @if($preview_product)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-6"
        x-data="{ active: 0 }" x-on:keydown.escape.window="$wire.closeProductPreview()">
        <div class="flex max-h-full w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" @click.outside="$wire.closeProductPreview()">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ $preview_product->name }}</h3>
                <button type="button" wire:click="closeProductPreview" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="overflow-y-auto p-5">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <div class="relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            @forelse($preview_product->images as $i => $image)
                            <img x-show="active === {{ $i }}" x-cloak src="{{ $image->url }}" alt="Imagen {{ $i + 1 }} de {{ $preview_product->name }}" class="h-full w-full object-cover">
                            @empty
                            <div class="flex h-full w-full items-center justify-center text-slate-300">
                                <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 6h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z"/></svg>
                            </div>
                            @endforelse
                        </div>

                        @if($preview_product->images->count() > 1)
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($preview_product->images as $i => $image)
                            <button type="button" x-on:click="active = {{ $i }}"
                                :class="active === {{ $i }} ? 'ring-2 ring-indigo-500' : 'ring-1 ring-slate-200'"
                                class="h-14 w-14 shrink-0 overflow-hidden rounded-lg">
                                <img src="{{ $image->url }}" alt="Miniatura {{ $i + 1 }} de {{ $preview_product->name }}" class="h-full w-full object-cover">
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-1.5">
                            @if($preview_product->product_type)
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $preview_product->product_type->name }}</span>
                            @endif
                            @if($preview_product->product_category)
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $preview_product->product_category->name }}</span>
                            @endif
                            @if($preview_product->brand)
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $preview_product->brand->name }}</span>
                            @endif
                        </div>

                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">SKU</dt>
                                <dd class="font-mono text-slate-800">{{ $preview_product->sku }}</dd>
                            </div>
                            @if($preview_product->barcode)
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Código de barras</dt>
                                <dd class="font-mono text-slate-800">{{ $preview_product->barcode }}</dd>
                            </div>
                            @endif
                            @if($preview_product->unit)
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Unidad</dt>
                                <dd class="text-slate-800">{{ $preview_product->unit->name }} ({{ $preview_product->unit->symbol }})</dd>
                            </div>
                            @endif
                            <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-semibold">
                                <dt class="text-slate-700">Precio</dt>
                                <dd class="text-indigo-700">{{ col_money($preview_product->sale_price) }}</dd>
                            </div>
                        </dl>

                        <div>
                            <h4 class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500">Descripción</h4>
                            <p class="whitespace-pre-line text-sm text-slate-700">{{ $preview_product->description ?: 'Sin descripción registrada.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-5 py-4">
                <div class="flex shrink-0 items-center rounded-lg border border-slate-200">
                    <button type="button" tabindex="-1"
                        x-on:click="const input = $el.nextElementSibling; input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1); input.dispatchEvent(new Event('input'))"
                        class="px-3 py-2 text-slate-500 hover:bg-slate-100">−</button>
                    <input type="number" readonly wire:model="catalog_quantities.{{ $preview_product->id }}" min="1" step="1"
                        class="w-10 border-0 bg-transparent p-0 text-center text-sm focus:outline-none focus:ring-0">
                    <button type="button" tabindex="-1"
                        x-on:click="const input = $el.previousElementSibling; input.value = (parseInt(input.value, 10) || 1) + 1; input.dispatchEvent(new Event('input'))"
                        class="px-3 py-2 text-slate-500 hover:bg-slate-100">+</button>
                </div>
                <button type="button" wire:click="addCatalogItem({{ $preview_product->id }})" class="btn btn-primary btn-sm flex-1 justify-center">
                    Agregar a la OT
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
