<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
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

    <header class="mb-4">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller · Equipos</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ $is_editing ? 'Editar equipo' : 'Nuevo equipo' }}
            </h1>
            <p class="mt-1 max-w-xl text-sm text-slate-600">
                Tipo: <span class="font-medium text-slate-800">{{ $equipment_type->name }}</span>
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
                <a href="{{ route('admin.workshop.equipment.type', $equipment_type) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Cancelar</a>
                @if($step > 1)
                <button type="button" wire:click="previousStep" class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Anterior</button>
                @endif
                @if($step < $total_steps)
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled" class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">Siguiente</button>
                @else
                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">
                    <span wire:loading.remove wire:target="save">{{ $is_editing ? 'Guardar' : 'Registrar' }}</span>
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
            <div class="grid grid-cols-1 gap-5 p-4 sm:p-6 md:grid-cols-2">
                @if($is_super_admin)
                <div class="relative md:col-span-2">
                    <label class="label-up">Negocio <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.business_id" class="form-select w-full border bg-white px-3 py-2 text-sm @error('form.business_id') border-rose-400 @enderror">
                        <option value="">Seleccionar negocio</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                    @error('form.business_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                @endif

                <div class="relative md:col-span-2">
                    <label class="label-up">Cliente <span class="text-rose-500">*</span></label>
                    <select wire:model="form.client_id"
                        @disabled($is_super_admin && ! $form->business_id)
                        class="form-select w-full border bg-white px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-60 @error('form.client_id') border-rose-400 @enderror">
                        <option value="">{{ $is_super_admin && ! $form->business_id ? 'Selecciona un negocio primero' : 'Seleccionar cliente' }}</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('form.client_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="relative md:col-span-2">
                    <label class="label-up">Nombre del equipo <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.name" placeholder="Ej. Camión principal, Unidad 01" class="form-input w-full border px-3 py-2 text-sm @error('form.name') border-rose-400 @enderror">
                    @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="relative">
                    <label class="label-up">Placa <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.plate" class="form-input w-full border px-3 py-2 text-sm uppercase @error('form.plate') border-rose-400 @enderror">
                    @error('form.plate') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>
        @endif

        @if($step === 2)
        <section class="rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="overflow-hidden rounded-t-2xl border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 2 de {{ $total_steps }}</p>
                <h2 class="font-semibold text-slate-800">Identificación</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 p-4 sm:p-6 md:grid-cols-2">
                <div class="relative">
                    <label class="label-up">Marca (catálogo) <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.brand_id"
                        @disabled($is_super_admin && ! $form->business_id)
                        class="form-select w-full border bg-white px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-60 @error('form.brand_id') border-rose-400 @enderror">
                        <option value="">{{ $is_super_admin && ! $form->business_id ? 'Selecciona un negocio primero' : 'Seleccionar marca' }}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    @error('form.brand_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="relative">
                    <label class="label-up">Modelo (catálogo) <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.model_id"
                        @disabled(! $form->brand_id)
                        class="form-select w-full border bg-white px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-60 @error('form.model_id') border-rose-400 @enderror">
                        <option value="">{{ $form->brand_id ? 'Seleccionar modelo' : 'Selecciona una marca primero' }}</option>
                        @foreach($models as $model)
                            <option value="{{ $model->id }}">{{ $model->name }}</option>
                        @endforeach
                    </select>
                    @error('form.model_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="relative">
                    <label class="label-up">Año <span class="text-rose-500">*</span></label>
                    <input type="number" wire:model="form.year" min="1900" max="{{ date('Y') + 1 }}" class="form-input w-full border px-3 py-2 text-sm @error('form.year') border-rose-400 @enderror">
                    @error('form.year') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>
        @endif

        @if($step === 3)
        <section class="rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="overflow-hidden rounded-t-2xl border-b border-slate-100 bg-slate-50/80 px-5 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 3 de {{ $total_steps }}</p>
                <h2 class="font-semibold text-slate-800">Atributos del tipo de equipo</h2>
                <p class="mt-0.5 text-xs text-slate-500">Campos configurados según el tipo seleccionado.</p>
            </div>
            <div class="space-y-4 p-4 sm:p-5">
                <x-ui.dynamic-attribute-fields
                    :links="$attribute_links"
                    wire-prefix="form.attribute_values"
                    embedded
                    compact
                />

                <div class="grid grid-cols-1 gap-3 border-t border-slate-100 pt-4 sm:grid-cols-3 sm:items-end">
                    <div class="relative sm:col-span-2">
                        <label class="label-up">Notas</label>
                        <textarea wire:model="form.notes" rows="2" class="form-input w-full border px-3 py-2 text-sm @error('form.notes') border-rose-400 @enderror"></textarea>
                        @error('form.notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <label class="mb-0.5 flex items-center gap-3 text-sm text-slate-700">
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
    </form>
</div>
