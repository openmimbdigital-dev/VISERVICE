<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.catalog-products.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Configuración</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.catalog-products.brands.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Marcas</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $form->isEditing() ? 'Editar' : 'Nueva' }}</span>
    </nav>

    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Configuración · Productos</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ $form->isEditing() ? 'Editar marca' : 'Nueva marca' }}
            </h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                Registra marcas para productos y servicios del catálogo.
            </p>
        </div>
        <a href="{{ route('admin.settings.catalog-products.brands.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm shrink-0">Volver</a>
    </header>

    <form wire:submit="save" class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Información básica</h2>
        </div>

        <div class="space-y-5 p-6">
            @if($is_super_admin)
            <p class="rounded-xl border border-indigo-100 bg-indigo-50 px-3.5 py-2.5 text-xs text-indigo-800">
                Las marcas creadas como superAdmin son <strong>generales</strong> y aplican a todos los negocios.
            </p>
            @else
            <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-600">
                La marca quedará asociada a tu negocio y al catálogo de productos.
            </p>
            @endif

            <p class="rounded-xl border border-amber-100 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                Esta marca se registrará para uso en <strong>Artículos / ítems</strong> del catálogo de productos.
            </p>

            <div class="relative">
                <label class="label-up">Nombre <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="form.name" placeholder="Ej. Donaldson"
                    class="form-input w-full border px-3 py-2 text-sm @error('form.name') border-rose-400 @enderror" />
                @error('form.name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Categorías <span class="text-rose-500">*</span></label>
                <p class="mb-3 text-sm text-slate-600">Selecciona las categorías de producto a las que aplica esta marca.</p>
                @if($product_categories->isNotEmpty())
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach($product_categories as $product_category)
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 transition hover:bg-slate-50">
                        <input type="checkbox" value="{{ $product_category->id }}" wire:model="form.product_category_ids"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">
                            {{ $product_category->name }}
                            @if(! $product_category->active)
                                <span class="text-xs text-slate-400">(inactiva)</span>
                            @endif
                        </span>
                    </label>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-500">No hay categorías disponibles. Crea categorías antes de registrar marcas.</p>
                @endif
                @error('form.product_category_ids')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="flex items-center gap-3 text-sm text-slate-700">
                    <span class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center">
                        <input type="checkbox" wire:model="form.active" class="peer sr-only">
                        <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-indigo-600"></span>
                        <span class="absolute left-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </span>
                    <span>{{ $form->active ? 'Activa' : 'Inactiva' }}</span>
                </label>
                @error('form.active')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-6 py-4 sm:flex-row sm:justify-end sm:gap-3">
            <a href="{{ route('admin.settings.catalog-products.brands.index') }}" wire:navigate class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Actualizar marca' : 'Crear marca' }}</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>
