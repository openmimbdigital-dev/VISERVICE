{{-- Ítems de la OT (solo lectura) vía work_order_id. Props: $items, $variant = card|compact --}}
@php
    $variant = $variant ?? 'card';
    $items = $items ?? collect();
@endphp

@if($variant === 'compact')
<table class="mt-4 w-full border-collapse text-xs">
    <thead>
        <tr class="border-b border-slate-300 bg-slate-50">
            <th class="px-2 py-1 text-left">#</th>
            <th class="px-2 py-1 text-left">Equipo</th>
            <th class="px-2 py-1 text-left">Descripción</th>
            <th class="px-2 py-1 text-left">Tipo</th>
            <th class="px-2 py-1 text-right">Cant.</th>
            <th class="px-2 py-1 text-right">Completados</th>
            <th class="px-2 py-1 text-right">Cancelados</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $index => $item)
        <tr class="border-b border-slate-100">
            <td class="px-2 py-1">{{ $index + 1 }}</td>
            <td class="px-2 py-1">{{ $item->equipment?->select_label ?? $item->equipment?->plate ?? '—' }}</td>
            <td class="px-2 py-1">
                {{ $item->description }}
                @if($item->technician_notes)
                <span class="block text-slate-500">{{ $item->technician_notes }}</span>
                @endif
            </td>
            <td class="px-2 py-1">{{ $item->productType?->name ?? '—' }}</td>
            <td class="px-2 py-1 text-right font-medium">{{ $item->quantity + 0 }}</td>
            <td class="px-2 py-1 text-right">{{ $item->quantity_complete + 0 }}</td>
            <td class="px-2 py-1 text-right">{{ $item->quantity_canceled + 0 }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="px-2 py-4 text-center text-slate-400">Sin ítems en la OT.</td>
        </tr>
        @endforelse
    </tbody>
    @if($items->isNotEmpty())
    <tfoot>
        <tr class="border-t border-slate-300">
            <td colspan="4" class="px-2 py-2 text-right font-semibold">Total ítems</td>
            <td class="px-2 py-2 text-right font-bold">{{ $items->sum(fn ($i) => (float) $i->quantity) + 0 }}</td>
            <td class="px-2 py-2 text-right font-bold">{{ $items->sum(fn ($i) => (float) $i->quantity_complete) + 0 }}</td>
            <td class="px-2 py-2 text-right font-bold">{{ $items->sum(fn ($i) => (float) $i->quantity_canceled) + 0 }}</td>
        </tr>
    </tfoot>
    @endif
</table>
@else
<section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
        <div>
            <h2 class="font-semibold text-slate-900">Ítems de la orden de trabajo</h2>
            <p class="mt-0.5 text-xs text-slate-500">Solo lectura — cantidades de la OT asociada</p>
        </div>
        <span class="text-sm text-slate-500">{{ $items->count() }} ítem(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50/40">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">#</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">Equipo</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">Descripción</th>
                    <th class="hidden px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 md:table-cell sm:px-4">Tipo</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 sm:px-4">Cant.</th>
                    <th class="px-3 py-2 text-center text-xs font-semibold uppercase text-slate-500 sm:px-4">Completados</th>
                    <th class="px-3 py-2 text-center text-xs font-semibold uppercase text-slate-500 sm:px-4">Cancelados</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $index => $item)
                <tr>
                    <td class="px-3 py-3 text-sm text-slate-500 sm:px-4">{{ $index + 1 }}</td>
                    <td class="px-3 py-3 text-xs text-slate-600 sm:px-4">{{ $item->equipment?->select_label ?? $item->equipment?->plate ?? '—' }}</td>
                    <td class="px-3 py-3 text-sm text-slate-900 sm:px-4">
                        <p>{{ $item->description }}</p>
                        @if($item->technician_notes)
                        <p class="mt-0.5 text-xs text-slate-400">{{ $item->technician_notes }}</p>
                        @endif
                    </td>
                    <td class="hidden px-3 py-3 text-sm text-slate-600 md:table-cell sm:px-4">{{ $item->productType?->name ?? '—' }}</td>
                    <td class="px-3 py-3 text-right text-sm font-semibold text-slate-900 sm:px-4">{{ $item->quantity + 0 }}</td>
                    <td class="px-3 py-3 text-center text-sm font-semibold text-emerald-700 sm:px-4">{{ $item->quantity_complete + 0 }}</td>
                    <td class="px-3 py-3 text-center text-sm font-semibold text-rose-600 sm:px-4">{{ $item->quantity_canceled + 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-400">
                        {{ ($empty_message ?? null) ?: 'Selecciona una OT para ver sus ítems.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endif
