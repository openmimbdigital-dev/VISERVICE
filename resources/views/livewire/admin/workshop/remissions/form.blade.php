<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.remissions.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Remisiones</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $is_editing ? ($reference ?? 'Editar') : 'Nueva remisión' }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                    {{ $is_editing ? 'Remisión ' . ($reference ?? '') : 'Nueva remisión' }}
                </h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Solo puedes asociar remisiones a OTs en estado creada o en proceso. Los ítems se toman de la OT.
                </p>
            </div>
            @if($is_editing)
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

    <form wire:submit="save" class="space-y-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-800">Datos de la remisión</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-6">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Orden de trabajo <span class="text-rose-500">*</span></label>
                    <select wire:model.live="form.work_order_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.work_order_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar OT</option>
                        @foreach($eligible_work_orders as $work_order)
                        <option value="{{ $work_order->id }}">
                            {{ $work_order->reference }} — {{ $work_order->client?->name }} / {{ $work_order->equipment?->plate }} ({{ $work_order->status_label }})
                        </option>
                        @endforeach
                    </select>
                    @error('form.work_order_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo <span class="text-rose-500">*</span></label>
                    <select wire:model="form.type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.type') border-rose-400 bg-rose-50 @enderror">
                        <option value="entrega">Entrega</option>
                        <option value="devolucion">Devolución</option>
                        <option value="traslado">Traslado</option>
                    </select>
                    @error('form.type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                    <div class="flex min-h-[42px] items-center rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5">
                        @if($form->work_order_id)
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $status_badge_class }}">{{ $status_label }}</span>
                            <span class="ml-2 text-xs text-slate-500">Heredado de la OT</span>
                        @else
                            <span class="text-sm text-slate-400">Selecciona una OT</span>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Cotización / Orden de compra</label>
                    <input type="text" wire:model="form.quotation_or_po_reference" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Fecha de expedición</label>
                    <input type="date" wire:model="form.issue_date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.issue_date') border-rose-400 bg-rose-50 @enderror">
                    @error('form.issue_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
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

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
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

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.workshop.remissions.index') }}" wire:navigate class="btn btn-outline-secondary w-full justify-center sm:w-auto">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary w-full justify-center sm:w-auto disabled:opacity-60">
                <span wire:loading.remove wire:target="save">{{ $is_editing ? 'Guardar cambios' : 'Crear remisión' }}</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>
