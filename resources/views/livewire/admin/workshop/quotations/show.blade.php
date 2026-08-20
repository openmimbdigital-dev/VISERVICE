<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.quotations.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Cotizaciones</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $quotation->reference }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $quotation->reference }}</h1>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $status_badge_class }}">{{ $quotation->status_label }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $quotation->client?->name }}
                    @if($quotation->equipments->isNotEmpty())
                        · {{ $quotation->equipments->map(fn ($e) => $e->select_label)->join(', ') }}
                    @endif
                    @if($quotation->hours_entry_formatted) · Horas: {{ $quotation->hours_entry_formatted }}@endif
                </p>
                @if((float) $quotation->advance_amount > 0)
                <p class="mt-2 text-sm text-amber-700">
                    Anticipo ({{ rtrim(rtrim(number_format((float) $quotation->advance_percentage, 2, '.', ''), '0'), '.') }}%):
                    <span class="font-semibold">{{ col_money($quotation->advance_amount) }}</span>
                </p>
                @endif
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.workshop.quotations.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Volver</a>
                @if($can_edit)
                    @if($edit_disabled)
                        <button
                            type="button"
                            disabled
                            title="{{ $edit_disabled_title }}"
                            class="btn btn-primary btn-sm flex-1 justify-center opacity-50 sm:flex-none"
                        >
                            Editar
                        </button>
                    @else
                        <a href="{{ route('admin.workshop.quotations.form.edit', $quotation->id) }}" wire:navigate class="btn btn-primary btn-sm flex-1 sm:flex-none justify-center">Editar</a>
                    @endif
                @endif
                @if($can_create_ot)
                <a href="{{ route('admin.workshop.work-orders.form', ['quotation' => $quotation->id]) }}" wire:navigate
                    class="btn btn-success btn-sm flex-1 sm:flex-none justify-center">
                    Crear OT
                </a>
                @elseif($linked_work_order)
                <a href="{{ route('admin.workshop.work-orders.show', $linked_work_order) }}" wire:navigate
                    class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">
                    Ver OT {{ $linked_work_order->reference }}
                </a>
                @endif
                <a href="{{ route('admin.workshop.quotations.print', $quotation->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Imprimir / PDF</a>
                @can('workshop.quotations.delete')
                @if($can_delete)
                <button type="button" wire:click="deleteQuotation" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">Eliminar</button>
                @else
                <button type="button" disabled title="{{ $edit_disabled_title }}; no se puede eliminar"
                    class="btn btn-danger btn-sm flex-1 justify-center opacity-50 sm:flex-none">Eliminar</button>
                @endif
                @endcan
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <h2 class="font-semibold text-slate-800">Datos generales</h2>
                </div>
                <dl class="divide-y divide-slate-100 px-4 py-2 sm:px-5">
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Cliente</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $quotation->client?->name ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Equipos</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">
                            @forelse($quotation->equipments as $equipment)
                                <span class="mb-1 mr-1 inline-block">{{ $equipment->select_label }}@if(! $loop->last), @endif</span>
                            @empty
                                —
                            @endforelse
                        </dd>
                    </div>
                    @if($quotation->hours_entry_formatted)
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Horas al ingreso</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $quotation->hours_entry_formatted }}</dd>
                    </div>
                    @endif
                    @if($quotation->notes)
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Notas internas</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $quotation->notes }}</dd>
                    </div>
                    @endif
                </dl>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <h2 class="font-semibold text-slate-800">Condiciones</h2>
                </div>
                <dl class="divide-y divide-slate-100 px-4 py-2 sm:px-5">
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Tipo de servicio</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $quotation->quotationServiceType?->name ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Vigencia</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $quotation->validity_days }} días @if($quotation->valid_until) (hasta {{ $quotation->valid_until->format('d/m/Y') }})@endif</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Forma de pago</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $quotation->paymentMethod?->name ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Anticipo</dt>
                        <dd class="text-sm sm:col-span-2 @if((float) $quotation->advance_amount > 0) font-medium text-amber-700 @else text-slate-900 @endif">
                            @if((float) $quotation->advance_amount > 0)
                                {{ rtrim(rtrim(number_format((float) $quotation->advance_percentage, 2, '.', ''), '0'), '.') }}%
                                — {{ col_money($quotation->advance_amount) }}
                            @else
                                Sin anticipo
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Cuenta bancaria</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">
                            @if($quotation->bankAccount)
                            {{ $quotation->bankAccount->bank_name }} — {{ $quotation->bankAccount->account_number }}
                            @else — @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Tiempo de ejecución</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $quotation->execution_time ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Diagnóstico</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $quotation->diagnosis ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-medium text-slate-500">Observaciones</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $quotation->observations ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <h2 class="font-semibold text-slate-800">Ítems</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/40">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">Equipo</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">Tipo</th>
                                <th class="hidden px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 md:table-cell sm:px-4">Categoría</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">Descripción</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 sm:px-4">Cant.</th>
                                <th class="hidden px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 sm:table-cell sm:px-4">P. Unit.</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 sm:px-4">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($quotation->items as $item)
                            <tr>
                                <td class="px-3 py-3 text-xs text-slate-600 sm:px-4">{{ $item->equipment?->select_label ?? '—' }}</td>
                                <td class="px-3 py-3 text-xs text-slate-600 sm:px-4">{{ $item->productType?->name ?? '—' }}</td>
                                <td class="hidden px-3 py-3 text-xs text-slate-600 md:table-cell sm:px-4">{{ $item->productCategory?->name ?? '—' }}</td>
                                <td class="px-3 py-3 text-sm text-slate-900 sm:px-4">{{ $item->description }}</td>
                                <td class="px-3 py-3 text-right text-sm text-slate-600 sm:px-4">{{ $item->quantity }}</td>
                                <td class="hidden px-3 py-3 text-right text-sm text-slate-600 sm:table-cell sm:px-4">{{ col_money($item->unit_price) }}</td>
                                <td class="px-3 py-3 text-right font-semibold text-slate-900 sm:px-4">{{ col_money($item->subtotal) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-slate-400">Sin ítems registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="space-y-4">
            @if($can_change_status)
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <h3 class="font-semibold text-slate-900">Estado de la cotización</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        @if($status_change_disabled)
                            Esta cotización está rechazada y ya no admite cambios de estado.
                        @else
                            Actualiza el seguimiento de la oferta.
                        @endif
                    </p>
                </div>
                @if($status_change_disabled)
                    <div class="space-y-3 p-4 sm:p-5">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                            <input type="text" disabled value="{{ $quotation->status_label }}"
                                class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 opacity-70">
                        </div>
                        @if($quotation->reject_reason)
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Motivo del rechazo</label>
                                <textarea disabled rows="3"
                                    class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 opacity-70">{{ $quotation->reject_reason }}</textarea>
                            </div>
                        @endif
                    </div>
                @else
                <form wire:submit="updateStatus" class="space-y-4 p-4 sm:p-5">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                        <select wire:model.live="status"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('status') border-rose-400 bg-rose-50 @enderror">
                            @foreach($status_options as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div @class(['hidden' => ! $show_reject_reason])>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Motivo del rechazo <span class="text-rose-500">*</span></label>
                        <textarea wire:model="reject_reason" rows="3"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('reject_reason') border-rose-400 bg-rose-50 @enderror"
                            placeholder="Describe por qué se rechazó la cotización"></textarea>
                        @error('reject_reason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                        <span wire:loading.remove wire:target="updateStatus">Guardar estado</span>
                        <span wire:loading wire:target="updateStatus">Guardando…</span>
                    </button>
                </form>
                @endif
            </section>
            @endif

            <section class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035]">
                <h3 class="font-semibold text-slate-900">Resumen</h3>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-xs text-slate-500"><dt>Mano de obra</dt><dd>{{ col_money($category_subtotals['mano_obra']) }}</dd></div>
                    <div class="flex justify-between text-xs text-slate-500"><dt>Repuestos</dt><dd>{{ col_money($category_subtotals['repuestos']) }}</dd></div>
                    <div class="flex justify-between text-xs text-slate-500"><dt>Lubricantes</dt><dd>{{ col_money($category_subtotals['lubricantes']) }}</dd></div>
                    <div class="flex justify-between text-xs text-slate-500"><dt>Otros</dt><dd>{{ col_money($category_subtotals['otros']) }}</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-2"><dt class="text-slate-500">Subtotal</dt><dd class="font-medium">{{ col_money($quotation->subtotal) }}</dd></div>
                    @if((float) $quotation->advance_amount > 0)
                    <div class="flex justify-between"><dt class="text-slate-500">Anticipo ({{ $quotation->advance_percentage }}%)</dt><dd class="font-medium text-amber-700">{{ col_money($quotation->advance_amount) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-slate-500">IVA ({{ $quotation->tax_percentage }}%)</dt><dd class="font-medium">{{ col_money($quotation->tax_amount) }}</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-bold"><dt>Total</dt><dd class="text-indigo-700">{{ col_money($quotation->total) }}</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035]">
                <h3 class="font-semibold text-slate-900">Detalles</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="text-xs text-slate-400">Creada</dt><dd>{{ $quotation->created_at->format('d/m/Y H:i') }}</dd></div>
                    @if($quotation->createdBy)
                    <div><dt class="text-xs text-slate-400">Creada por</dt><dd>{{ $quotation->createdBy->name }}</dd></div>
                    @endif
                    @if($quotation->reject_reason)
                    <div><dt class="text-xs text-slate-400">Motivo rechazo</dt><dd class="text-slate-700">{{ $quotation->reject_reason }}</dd></div>
                    @endif
                </dl>
            </section>
        </div>
    </div>
</div>
