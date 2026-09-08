<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Catálogo</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.catalog.products.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Productos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $is_editing ? 'Editar producto' : 'Nuevo producto' }}</span>
    </nav>

    <header class="mb-8">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Catálogo</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ $is_editing ? 'Editar producto' : 'Nuevo producto' }}
            </h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                {{ $is_editing ? 'Actualiza la información del producto en el catálogo.' : 'Registra un nuevo producto o servicio en el catálogo.' }}
            </p>
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
                <a href="{{ route('admin.catalog.products.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Cancelar</a>
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
        @if($step === 1)
        <section class="rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="overflow-hidden rounded-t-2xl border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 1 de {{ $total_steps }}</p>
                <h2 class="font-semibold text-slate-800">Información general</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                @if($is_super_admin)
                <div class="relative md:col-span-2">
                    <label class="label-up">Comercio <span class="text-rose-500">*</span></label>
                    <select wire:model="form.business_id" class="form-select w-full border bg-white px-3 py-2 text-sm @error('form.business_id') border-rose-400 @enderror">
                        <option value="">Seleccionar comercio</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                    @error('form.business_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                @endif

                <div class="relative">
                    <label class="label-up">SKU <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model.blur="form.sku" placeholder="Ej. REP-FIL-ACEITE" class="form-input w-full border px-3 py-2 text-sm font-mono" />
                    <p class="mt-1 text-xs text-slate-500">Debe ser único en el comercio.</p>
                    @error('form.sku')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
                    <label class="label-up">Código de barras <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model.blur="form.barcode" placeholder="EAN / UPC" class="form-input w-full border px-3 py-2 text-sm font-mono" />
                    <p class="mt-1 text-xs text-slate-500">Debe ser único en el comercio.</p>
                    @error('form.barcode')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative md:col-span-2">
                    <label class="label-up">Nombre <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.name" class="form-input w-full border px-3 py-2 text-sm" />
                    @error('form.name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative md:col-span-2">
                    <label class="label-up">Descripción</label>
                    <textarea wire:model="form.description" rows="3" class="form-input w-full border px-3 py-2 text-sm"></textarea>
                    @error('form.description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>
        @endif

        @if($step === 2)
        <section class="rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="overflow-hidden rounded-t-2xl border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 2 de {{ $total_steps }}</p>
                <h2 class="font-semibold text-slate-800">Clasificación</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                <div class="relative">
                    <label class="label-up">Tipo de producto <span class="text-rose-500">*</span></label>
                    <select wire:model="form.product_type_id" class="form-select w-full border bg-white px-3 py-2 text-sm @error('form.product_type_id') border-rose-400 @enderror">
                        <option value="">Seleccionar tipo</option>
                        @foreach($product_types as $product_type)
                            <option value="{{ $product_type->id }}">{{ $product_type->name }}@if(! $product_type->active) (inactivo)@endif</option>
                        @endforeach
                    </select>
                    @error('form.product_type_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
                    <label class="label-up">Categoría <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.product_category_id" class="form-select w-full border bg-white px-3 py-2 text-sm @error('form.product_category_id') border-rose-400 @enderror">
                        <option value="">Seleccionar categoría</option>
                        @foreach($product_categories as $product_category)
                            <option value="{{ $product_category->id }}">{{ $product_category->name }}@if(! $product_category->active) (inactiva)@endif</option>
                        @endforeach
                    </select>
                    @error('form.product_category_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    @php $selected_category = $product_categories->firstWhere('id', (int) $form->product_category_id); @endphp
                    @if($selected_category)
                    <p class="mt-1.5 text-xs text-slate-500">
                        Control de inventario: <strong>{{ $selected_category->inventory ? 'Sí' : 'No' }}</strong> (definido por la categoría)
                    </p>
                    @endif
                </div>

                <div class="relative">
                    <label class="label-up">Unidad de medida <span class="text-rose-500">*</span></label>
                    <select wire:model="form.unit_id" class="form-select w-full border bg-white px-3 py-2 text-sm @error('form.unit_id') border-rose-400 @enderror">
                        <option value="">Seleccionar unidad</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})@if(! $unit->active) (inactiva)@endif</option>
                        @endforeach
                    </select>
                    @error('form.unit_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
                    <label class="label-up">Marca</label>
                    <select wire:model="form.brand_id" class="form-select w-full border bg-white px-3 py-2 text-sm @error('form.brand_id') border-rose-400 @enderror">
                        <option value="">Sin marca</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}@if(! $brand->active) (inactiva)@endif</option>
                        @endforeach
                    </select>
                    @error('form.brand_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>
        @endif

        @if($step === 3)
        <section class="rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="overflow-hidden rounded-t-2xl border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 3 de {{ $total_steps }}</p>
                <h2 class="font-semibold text-slate-800">Precios</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                <div class="relative">
                    <label class="label-up">Precio de costo <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" inputmode="decimal" wire:model.live.debounce.400ms="form.cost_price" class="form-input w-full border px-3 py-2 text-sm [-moz-appearance:text-field] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                    @error('form.cost_price')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
                    <label class="label-up">Porcentaje de ganancia <span class="text-rose-500">*</span></label>
                    <div class="form-input-suffix @error('form.profit_percentage') border-rose-400 @enderror">
                        <input type="number" step="0.01" min="0" inputmode="decimal"
                            wire:model.live.debounce.400ms="form.profit_percentage"
                            class="form-input px-3 py-2 text-sm [-moz-appearance:text-field] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                        <span class="form-input-suffix-addon">%</span>
                    </div>
                    @error('form.profit_percentage')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
                    <label class="label-up">Margen de ganancia</label>
                    <p class="flex min-h-[42px] items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm tabular-nums font-medium text-slate-800">
                        {{ $form->profitMarginAmount() !== null ? '$ ' . number_format($form->profitMarginAmount(), 2, ',', '.') : '—' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Se calcula como costo × porcentaje.</p>
                </div>

                <div class="relative">
                    <label class="label-up">Precio de venta</label>
                    <p class="flex min-h-[42px] items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm tabular-nums font-medium text-slate-800">
                        {{ $form->sale_price !== '' ? '$ ' . number_format((float) $form->sale_price, 2, ',', '.') : '—' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Se calcula como costo + margen.</p>
                    @error('form.sale_price')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Descuento</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Opcional. Al agregar el producto a una cotización u orden de trabajo se aplica este descuento; si no hay descuento, la línea queda al precio de venta.
                        </p>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="relative">
                                <label class="label-up">Forma del descuento</label>
                                <select wire:model.live="form.discount_type" class="form-select w-full border bg-white px-3 py-2 text-sm @error('form.discount_type') border-rose-400 @enderror">
                                    <option value="">Sin descuento</option>
                                    <option value="percentage">Porcentaje (%)</option>
                                    <option value="amount">Valor fijo por unidad</option>
                                </select>
                                @error('form.discount_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            @if($form->discount_type !== '')
                            <div class="relative">
                                <label class="label-up">
                                    {{ $form->discount_type === 'percentage' ? 'Porcentaje de descuento' : 'Valor del descuento' }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" min="0"
                                    @if($form->discount_type === 'percentage') max="100" @endif
                                    wire:model.live.debounce.400ms="form.discount_value"
                                    class="form-input w-full border px-3 py-2 text-sm tabular-nums [-moz-appearance:text-field] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                                @error('form.discount_value')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            @endif
                        </div>

                        @if($discount_preview)
                        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 border-t border-slate-200 pt-3 text-sm">
                            <span class="text-slate-500">Precio de venta: <span class="tabular-nums text-slate-700 line-through">{{ col_money($discount_preview['sale_price']) }}</span></span>
                            <span class="text-slate-500">Descuento: <span class="tabular-nums font-medium text-amber-700">−{{ col_money($discount_preview['discount']) }}</span></span>
                            <span class="font-semibold text-slate-900">Precio final: <span class="tabular-nums text-emerald-700">{{ col_money($discount_preview['final']) }}</span></span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="relative flex flex-col justify-end gap-3 md:col-span-2">
                    <label class="flex items-center gap-3 text-sm text-slate-700">
                        <span class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center">
                            <input type="checkbox" wire:model="form.status" class="peer sr-only">
                            <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-indigo-600"></span>
                            <span class="absolute left-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                        </span>
                        <span>{{ $form->status ? 'Activo' : 'Inactivo' }}</span>
                    </label>
                </div>
            </div>
        </section>
        @endif

        @if($step === 4)
        <section class="rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="overflow-hidden rounded-t-2xl border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 4 de {{ $total_steps }}</p>
                <h2 class="font-semibold text-slate-800">Imágenes</h2>
                <p class="mt-1 text-xs text-slate-500">Opcional. La imagen marcada como principal es la que se muestra en los listados y en el selector de productos de la OT.</p>
            </div>
            <div class="p-6">
                @if($product)
                <livewire:admin.catalog.products.image-gallery :product="$product" :key="'product-gallery-form-'.$product->id" />
                @else
                <p class="text-sm text-slate-400">Guarda la información general del producto para poder agregar imágenes.</p>
                @endif
            </div>
        </section>
        @endif
    </form>
</div>
