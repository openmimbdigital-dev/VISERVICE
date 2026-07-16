@php
$statusBadge = [
    'borrador'  => 'bg-slate-100 text-slate-600 ring-slate-500/20',
    'emitida'   => 'bg-blue-50 text-blue-700 ring-blue-600/20',
    'entregada' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
];
$badge = $statusBadge[$remission->status] ?? 'bg-slate-100 text-slate-600 ring-slate-500/20';
@endphp

<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.remissions.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Remisiones</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $remission->reference }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-mono text-2xl font-bold text-slate-900">{{ $remission->reference }}</h1>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium ring-1 {{ $badge }}">
                        {{ $remission->status_label }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">
                        {{ $remission->type_label }}
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-600">
                    <span class="font-medium text-slate-800">{{ $remission->client?->name }}</span>
                    <span>{{ $remission->equipment?->plate }}</span>
                    @if($remission->workOrder)
                    <a href="{{ route('admin.workshop.work-orders.show', $remission->work_order_id) }}" wire:navigate class="text-indigo-600 hover:underline">
                        OT {{ $remission->workOrder->reference }}
                    </a>
                    @endif
                    @if($remission->issue_date)
                    <span>Expedición: {{ $remission->issue_date->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.workshop.remissions.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Volver</a>
                <a href="{{ route('admin.workshop.remissions.print', $remission) }}" target="_blank" class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Imprimir / PDF</a>
                @if($can_edit)
                <a href="{{ route('admin.workshop.remissions.form.edit', $remission) }}" wire:navigate class="btn btn-primary btn-sm flex-1 sm:flex-none justify-center">Editar</a>
                @endif
                @if($can_delete)
                <button type="button" wire:click="deleteRemission" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">Eliminar</button>
                @endif
            </div>
        </div>
    </header>

    @if(! empty($document_client))
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
            <h2 class="font-semibold text-slate-900">Documento del cliente (OT)</h2>
        </div>
        <dl class="divide-y divide-slate-100 px-4 py-2 sm:px-5">
            @foreach($document_client as $label => $value)
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">{{ $document_labels[$label] ?? $label }}</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $value }}</dd>
            </div>
            @endforeach
        </dl>
    </section>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Destino</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                @foreach([
                    ['Dirección', $remission->delivery_address],
                    ['Ciudad', $remission->delivery_city],
                    ['Contacto', $remission->delivery_contact],
                    ['Teléfono', $remission->delivery_phone],
                    ['Obs. entrega', $remission->delivery_observations],
                ] as [$label, $value])
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">{{ $label }}</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $value ?: '—' }}</dd>
                </div>
                @endforeach
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Responsables</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                @foreach([
                    ['Entregado por', $remission->delivered_by_name],
                    ['Cargo', $remission->delivered_by_position],
                    ['C.C.', $remission->delivered_by_document],
                    ['Recibido por', $remission->received_by_name],
                    ['Cargo', $remission->received_by_position],
                    ['C.C.', $remission->received_by_document],
                ] as [$label, $value])
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">{{ $label }}</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $value ?: '—' }}</dd>
                </div>
                @endforeach
            </dl>
        </section>
    </div>

    @if($remission->observations)
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Observaciones generales</h2>
        </div>
        <p class="px-5 py-4 text-sm text-slate-700">{{ $remission->observations }}</p>
    </section>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
            <h2 class="font-semibold text-slate-900">Ítems</h2>
            <span class="text-sm text-slate-500">Total: {{ $remission->total_items }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/40">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">#</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">Descripción</th>
                        <th class="hidden px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 md:table-cell sm:px-4">Tipo</th>
                        <th class="hidden px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 lg:table-cell sm:px-4">Categoría</th>
                        <th class="hidden px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:table-cell sm:px-4">Ref./Marca</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 sm:px-4">Cant.</th>
                        <th class="hidden px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 md:table-cell sm:px-4">Unidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($remission->items as $index => $item)
                    <tr>
                        <td class="px-3 py-3 text-sm text-slate-500 sm:px-4">{{ $index + 1 }}</td>
                        <td class="px-3 py-3 text-sm text-slate-900 sm:px-4">
                            <p>{{ $item->description }}</p>
                            @if($item->observations)
                            <p class="mt-0.5 text-xs text-slate-400">{{ $item->observations }}</p>
                            @endif
                        </td>
                        <td class="hidden px-3 py-3 text-sm text-slate-600 md:table-cell sm:px-4">{{ $item->productType?->name ?? '—' }}</td>
                        <td class="hidden px-3 py-3 text-sm text-slate-600 lg:table-cell sm:px-4">{{ $item->productCategory?->name ?? '—' }}</td>
                        <td class="hidden px-3 py-3 text-sm text-slate-600 sm:table-cell sm:px-4">{{ $item->reference_brand ?? '—' }}</td>
                        <td class="px-3 py-3 text-right text-sm font-semibold text-slate-900 sm:px-4">{{ $item->quantity }}</td>
                        <td class="hidden px-3 py-3 text-sm text-slate-600 md:table-cell sm:px-4">{{ $item->unit_name ?? $item->unit?->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-400">Sin ítems.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
