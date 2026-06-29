<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.equipment.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Equipos</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.equipment.type', $equipment_type) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">{{ $equipment_type->name }}</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $is_editing ? 'Editar' : 'Nuevo' }}</span>
    </nav>

    <header class="mb-8">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller · Equipos</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ $is_editing ? 'Editar equipo' : 'Nuevo equipo' }}
            </h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                Tipo: <span class="font-medium text-slate-800">{{ $equipment_type->name }}</span>
            </p>
        </div>
    </header>

    <form wire:submit="save" class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Datos del equipo</h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-4 sm:p-6 md:grid-cols-2">
            @if($is_super_admin)
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Negocio <span class="text-rose-500">*</span></label>
                <select wire:model.live="form.business_id"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.business_id') border-rose-400 bg-rose-50 @enderror">
                    <option value="">Seleccionar negocio</option>
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}">{{ $business->name }}</option>
                    @endforeach
                </select>
                @error('form.business_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            @endif

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Cliente <span class="text-rose-500">*</span></label>
                <select wire:model="form.client_id"
                    @disabled($is_super_admin && ! $form->business_id)
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-60 @error('form.client_id') border-rose-400 bg-rose-50 @enderror">
                    <option value="">{{ $is_super_admin && ! $form->business_id ? 'Selecciona un negocio primero' : 'Seleccionar cliente' }}</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('form.client_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Placa <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="form.plate"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm uppercase transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.plate') border-rose-400 bg-rose-50 @enderror">
                @error('form.plate') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Marca (catálogo)</label>
                <select wire:model.live="form.brand_id"
                    @disabled($is_super_admin && ! $form->business_id)
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-60 @error('form.brand_id') border-rose-400 bg-rose-50 @enderror">
                    <option value="">Sin marca del catálogo</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
                @error('form.brand_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Modelo (catálogo)</label>
                <select wire:model.live="form.model_id"
                    @disabled(! $form->brand_id)
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-60 @error('form.model_id') border-rose-400 bg-rose-50 @enderror">
                    <option value="">{{ $form->brand_id ? 'Seleccionar modelo' : 'Selecciona una marca primero' }}</option>
                    @foreach($models as $model)
                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                    @endforeach
                </select>
                @error('form.model_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Año</label>
                <input type="number" wire:model="form.year" min="1900" max="{{ date('Y') + 1 }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.year') border-rose-400 bg-rose-50 @enderror">
                @error('form.year') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$toggle('form.status')"
                        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200 {{ $form->status ? 'bg-indigo-600' : 'bg-slate-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $form->status ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                    <span class="text-sm {{ $form->status ? 'font-medium text-emerald-700' : 'text-slate-500' }}">
                        {{ $form->status ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Notas</label>
                <textarea wire:model="form.notes" rows="3"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.notes') border-rose-400 bg-rose-50 @enderror"></textarea>
                @error('form.notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <a href="{{ route('admin.workshop.equipment.type', $equipment_type) }}" wire:navigate
                class="btn btn-outline-secondary w-full justify-center sm:w-auto">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled"
                class="btn btn-primary w-full justify-center sm:w-auto">
                {{ $is_editing ? 'Guardar cambios' : 'Registrar equipo' }}
            </button>
        </div>
    </form>
</div>
