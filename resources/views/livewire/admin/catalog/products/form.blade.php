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

    <form wire:submit="save" class="space-y-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Información general</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                @if($is_super_admin)
                <div class="relative md:col-span-2">
                    <label class="label-up">Comercio <span class="text-rose-500">*</span></label>
                    <select wire:model="form.business_id" class="form-select w-full border px-3 py-2 text-sm">
                        <option value="">Seleccionar comercio</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                    @error('form.business_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                @endif

                <div class="relative">
                    <label class="label-up">Código <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.code" class="form-input w-full border px-3 py-2 text-sm font-mono" />
                    @error('form.code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
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

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Clasificación</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                <div class="relative">
                    <label class="label-up">Tipo de producto <span class="text-rose-500">*</span></label>
                    <select wire:model="form.product_type_id" class="form-select w-full border px-3 py-2 text-sm">
                        <option value="">Seleccionar tipo</option>
                        @foreach($product_types as $product_type)
                            <option value="{{ $product_type->id }}">{{ $product_type->name }}@if(! $product_type->active) (inactivo)@endif</option>
                        @endforeach
                    </select>
                    @error('form.product_type_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
                    <label class="label-up">Categoría <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.product_category_id" class="form-select w-full border px-3 py-2 text-sm">
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
                    <select wire:model="form.unit_id" class="form-select w-full border px-3 py-2 text-sm">
                        <option value="">Seleccionar unidad</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})@if(! $unit->active) (inactiva)@endif</option>
                        @endforeach
                    </select>
                    @error('form.unit_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
                    <label class="label-up">Marca</label>
                    <select wire:model="form.brand_id" class="form-select w-full border px-3 py-2 text-sm">
                        <option value="">Sin marca</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}@if(! $brand->active) (inactiva)@endif</option>
                        @endforeach
                    </select>
                    @error('form.brand_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Precios</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                <div class="relative">
                    <label class="label-up">Precio de costo <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" wire:model="form.cost_price" class="form-input w-full border px-3 py-2 text-sm" />
                    @error('form.cost_price')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
                    <label class="label-up">Precio de venta <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" wire:model="form.sale_price" class="form-input w-full border px-3 py-2 text-sm" />
                    @error('form.sale_price')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
                    <label class="label-up">ID impuesto</label>
                    <input type="number" wire:model="form.tax_id" class="form-input w-full border px-3 py-2 text-sm" placeholder="Opcional" />
                    @error('form.tax_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
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

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3">
            <a href="{{ route('admin.catalog.products.index') }}" wire:navigate class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                <span wire:loading.remove wire:target="save">{{ $is_editing ? 'Actualizar producto' : 'Crear producto' }}</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>
