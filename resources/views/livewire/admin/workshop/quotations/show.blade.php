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
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600">{{ $quotation->status_label }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $quotation->client?->name }} · {{ $quotation->equipment?->plate }} {{ $quotation->equipment?->brand }} {{ $quotation->equipment?->model }}
                    · Km: {{ number_format($quotation->km_entry) }}
                    @if($quotation->hours_entry) · Horas: {{ number_format($quotation->hours_entry) }}@endif
                </p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.workshop.quotations.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Volver</a>
                @can('workshop.quotations.edit')
                @if($can_edit)
                <a href="{{ route('admin.workshop.quotations.form.edit', $quotation->id) }}" wire:navigate class="btn btn-primary btn-sm flex-1 sm:flex-none justify-center">Editar</a>
                @endif
                @endcan
                <a href="{{ route('admin.workshop.quotations.print', $quotation->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Imprimir / PDF</a>
                @can('workshop.quotations.delete')
                @if($can_delete)
                <button type="button" wire:click="deleteQuotation" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">Eliminar</button>
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
                        <dt class="text-xs font-medium text-slate-500">Equipo</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">
                            {{ $quotation->equipment?->plate }} — {{ $quotation->equipment?->brand }} {{ $quotation->equipment?->model }}
                        </dd>
                    </div>
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
                                <td class="px-3 py-3 text-xs text-slate-600 sm:px-4">{{ $item->itemType?->name ?? '—' }}</td>
                                <td class="hidden px-3 py-3 text-xs text-slate-600 md:table-cell sm:px-4">{{ $item->itemCategory?->name ?? '—' }}</td>
                                <td class="px-3 py-3 text-sm text-slate-900 sm:px-4">{{ $item->description }}</td>
                                <td class="px-3 py-3 text-right text-sm text-slate-600 sm:px-4">{{ $item->quantity }}</td>
                                <td class="hidden px-3 py-3 text-right text-sm text-slate-600 sm:table-cell sm:px-4">{{ col_money($item->unit_price) }}</td>
                                <td class="px-3 py-3 text-right font-semibold text-slate-900 sm:px-4">{{ col_money($item->subtotal) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-400">Sin ítems registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="space-y-4">
            <section class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/[0.035]">
                <h3 class="font-semibold text-slate-900">Resumen</h3>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-xs text-slate-500"><dt>Mano de obra</dt><dd>{{ col_money($category_subtotals['mano_obra']) }}</dd></div>
                    <div class="flex justify-between text-xs text-slate-500"><dt>Repuestos</dt><dd>{{ col_money($category_subtotals['repuestos']) }}</dd></div>
                    <div class="flex justify-between text-xs text-slate-500"><dt>Lubricantes</dt><dd>{{ col_money($category_subtotals['lubricantes']) }}</dd></div>
                    <div class="flex justify-between text-xs text-slate-500"><dt>Otros</dt><dd>{{ col_money($category_subtotals['otros']) }}</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-2"><dt class="text-slate-500">Subtotal</dt><dd class="font-medium">{{ col_money($quotation->subtotal) }}</dd></div>
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
                </dl>
            </section>
        </div>
    </div>
</div>
