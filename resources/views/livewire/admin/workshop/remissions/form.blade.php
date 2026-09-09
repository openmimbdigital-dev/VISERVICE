<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.remissions.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Remisiones</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $is_editing ? ($reference ?? 'Editar') : 'Nueva remisión' }}</span>
    </nav>

    <header class="mb-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ $is_editing ? 'Remisión ' . ($reference ?? '') : 'Nueva remisión' }}
                </h1>
                @if($client_name)
                <p class="mt-1 text-sm font-medium text-slate-700">{{ $client_name }}</p>
                @endif
                @if($is_editing && $status_label)
                <p class="mt-1">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $status_badge_class }}">{{ $status_label }}</span>
                </p>
                @else
                <p class="mt-1 max-w-xl text-sm text-slate-600">
                    Completa la remisión por pasos. Los ítems se toman de la OT.
                </p>
                @endif
            </div>
            @if($is_editing && $saved_complete)
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.workshop.remissions.show', $form->remission_id) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Ver detalle</a>
                <a href="{{ route('admin.workshop.remissions.print', $form->remission_id) }}" target="_blank" class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Imprimir / PDF</a>
                @can('workshop.remissions.delete')
                @if($can_delete)
                <button type="button" wire:click="deleteRemission" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">Eliminar</button>
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
                        $is_done = $completed_steps >= $number;
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
                    <a href="{{ $is_editing ? route('admin.workshop.remissions.show', $form->remission_id) : route('admin.workshop.remissions.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Cancelar</a>
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
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 1 de {{ $total_steps }}</p>
                <h2 class="font-semibold text-slate-800">Datos de la remisión</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-6">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Orden de trabajo <span class="text-rose-500">*</span></label>
                    @if($work_order_locked)
                    <input type="hidden" wire:model="form.work_order_id">
                    @endif
                    <select wire:model.live="form.work_order_id"
                        @disabled($work_order_locked)
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm disabled:cursor-not-allowed disabled:opacity-70 @error('form.work_order_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar OT</option>
                        @foreach($eligible_work_orders as $work_order)
                        <option value="{{ $work_order->id }}">
                            {{ $work_order->reference }} — {{ $work_order->client?->name }}
                            @if($work_order->equipments->isNotEmpty())
                                / {{ $work_order->equipments->pluck('plate')->filter()->join(', ') }}
                            @endif
                            ({{ $work_order->status_label }})
                        </option>
                        @endforeach
                    </select>
                    @if($work_order_locked)
                    <p class="mt-1 text-xs text-slate-500">OT fijada desde la orden de trabajo.</p>
                    @endif
                    @error('form.work_order_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo</label>
                    <div class="flex min-h-[42px] items-center rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5">
                        <span class="text-sm text-slate-700">
                            @switch($form->type)
                                @case('devolucion') Devolución @break
                                @case('traslado') Traslado @break
                                @default Entrega
                            @endswitch
                        </span>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                    <div class="flex min-h-[42px] items-center rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5">
                        @if($form->work_order_id)
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $status_badge_class }}">{{ $status_label }}</span>
                            @if($saved_complete)
                            <span class="ml-2 text-xs text-slate-500">Heredado de la OT</span>
                            @endif
                        @else
                            <span class="text-sm text-slate-400">Selecciona una OT</span>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Cotización / Orden de compra</label>
                    <div class="flex min-h-[42px] items-center rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5">
                        @if($form->quotation_or_po_reference)
                            <span class="text-sm text-slate-700">{{ $form->quotation_or_po_reference }}</span>
                        @else
                            <span class="text-sm text-slate-400">Sin cotización asociada a la OT</span>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Fecha de expedición</label>
                    <input type="date" wire:model="form.issue_date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.issue_date') border-rose-400 bg-rose-50 @enderror">
                    @error('form.issue_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>
        @endif

        @if($step === 2)
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 2 de {{ $total_steps }}</p>
                <h2 class="font-semibold text-slate-800">Destino / entrega</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-6">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Dirección de entrega</label>
                    <input type="text" wire:model="form.delivery_address" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Ciudad</label>
                    <select wire:model="form.delivery_city"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.delivery_city') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar ciudad</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->name }}">
                            {{ $city->name }}{{ $city->state_province ? ' — ' . $city->state_province : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('form.delivery_city') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Contacto en destino</label>
                    <input type="text" wire:model="form.delivery_contact" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Teléfono</label>
                    <input type="text" wire:model="form.delivery_phone" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Observaciones de entrega</label>
                    <textarea wire:model="form.delivery_observations" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm"></textarea>
                </div>
            </div>
        </section>
        @endif

        @if($step === 3)
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Paso 3 de {{ $total_steps }}</p>
                <h2 class="font-semibold text-slate-800">Responsables</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-6">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Entregado por — Nombre <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.delivered_by_name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.delivered_by_name') border-rose-400 bg-rose-50 @enderror">
                    @error('form.delivered_by_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Entregado por — Cargo <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.delivered_by_position" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.delivered_by_position') border-rose-400 bg-rose-50 @enderror">
                    @error('form.delivered_by_position') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Entregado por — C.C. <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.delivered_by_document" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.delivered_by_document') border-rose-400 bg-rose-50 @enderror">
                    @error('form.delivered_by_document') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Recibido por — Nombre <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.received_by_name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.received_by_name') border-rose-400 bg-rose-50 @enderror">
                    @error('form.received_by_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Recibido por — Cargo <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.received_by_position" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.received_by_position') border-rose-400 bg-rose-50 @enderror">
                    @error('form.received_by_position') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Recibido por — C.C. <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.received_by_document" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.received_by_document') border-rose-400 bg-rose-50 @enderror">
                    @error('form.received_by_document') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Observaciones generales</label>
                    <textarea wire:model="form.observations" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm"></textarea>
                </div>
            </div>
        </section>
        @endif

        @if($step === 4)
        @include('livewire.admin.workshop.remissions.partials.work-order-items', [
            'items' => $work_order_items,
            'variant' => 'card',
            'step_label' => 'Paso 4 de '.$total_steps,
            'empty_message' => 'Selecciona una OT para ver sus ítems.',
        ])
        @endif
    </form>
</div>
