@php
    $format_qty = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
@endphp

<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.invoices.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Facturación</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $invoice->reference }}</span>
    </nav>

    {{-- Encabezado: identidad, total, recorrido y acciones --}}
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 px-4 py-6 sm:px-6 sm:py-7">
            <div class="pointer-events-none absolute -right-20 -top-24 h-60 w-60 rounded-full bg-indigo-500/20 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-28 left-1/3 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl" aria-hidden="true"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-300/80">Taller · Factura</p>

                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <h1 class="font-mono text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $invoice->reference }}</h1>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $invoice->status_badge_class }}">
                            {{ $invoice->status_label }}
                        </span>
                        @if($electronic_invoice)
                        <a href="#inv-dian" x-on:click.prevent="document.getElementById('inv-dian')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition hover:opacity-80 {{ $electronic_invoice->status->badgeClass() }}"
                            title="{{ $electronic_invoice->document_number }} — ver detalle DIAN">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            DIAN: {{ $electronic_invoice->status->label() }}
                        </a>
                        @elseif($dian_applies)
                        <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-slate-300 ring-1 ring-white/15">
                            Sin emitir ante la DIAN
                        </span>
                        @endif
                    </div>

                    <dl class="mt-5 flex flex-wrap gap-x-8 gap-y-4">
                        <div class="min-w-0">
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                {{ $invoice->bill_to_final_consumer ? 'Facturada a' : 'Cliente' }}
                            </dt>
                            <dd class="mt-0.5 text-sm font-semibold text-white">
                                @if($invoice->bill_to_final_consumer)
                                    {{ config('dian.final_consumer.name') }}
                                    <span class="font-normal text-slate-400">· NIT {{ config('dian.final_consumer.document_number') }}</span>
                                @else
                                    {{ $invoice->workOrder?->client?->name ?? '—' }}
                                @endif
                            </dd>
                            @if($invoice->bill_to_final_consumer && $invoice->workOrder?->client)
                            <dd class="mt-0.5 text-[11px] text-slate-400">
                                Cliente de la OT: {{ $invoice->workOrder->client->name }}
                            </dd>
                            @endif
                        </div>
                        @if($invoice->workOrder)
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Orden de trabajo</dt>
                            <dd class="mt-0.5">
                                <a href="{{ route('admin.workshop.work-orders.show', $invoice->work_order_id) }}" wire:navigate
                                    class="inline-flex items-center gap-1 font-mono text-sm text-indigo-200 transition hover:text-white">
                                    {{ $invoice->workOrder->reference }}
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Creada</dt>
                            <dd class="mt-0.5 text-sm text-slate-200">
                                {{ $invoice->created_at->format('d/m/Y H:i') }}
                                @if($invoice->createdBy)
                                <span class="text-slate-400">· {{ $invoice->createdBy->name }}</span>
                                @endif
                            </dd>
                        </div>
                        @if($electronic_invoice)
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Número autorizado</dt>
                            <dd class="mt-0.5 font-mono text-sm text-slate-200">{{ $electronic_invoice->document_number }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <div class="shrink-0 rounded-2xl bg-white/5 px-5 py-4 ring-1 ring-white/10 lg:min-w-[16rem] lg:text-right">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total facturado</p>
                    <p class="mt-1 text-3xl font-bold tracking-tight text-white">{{ col_money($invoice->total) }}</p>
                    @if($due_state['label'])
                    <div class="mt-3 border-t border-white/10 pt-3">
                        <p class="text-xs {{ $due_state['is_overdue'] ? 'text-rose-300' : 'text-slate-300' }}">
                            Vence: <span class="font-semibold">{{ $due_state['label'] }}</span>
                        </p>
                        @if($due_state['note'])
                        <p class="mt-0.5 text-[11px] {{ $due_state['is_overdue'] ? 'font-semibold text-rose-300' : 'text-slate-400' }}">
                            {{ $due_state['note'] }}
                        </p>
                        @endif
                    </div>
                    @elseif($invoice->paid_at)
                    <div class="mt-3 border-t border-white/10 pt-3">
                        <p class="text-xs text-emerald-300">Pagada el <span class="font-semibold">{{ $invoice->paid_at->format('d/m/Y') }}</span></p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Recorrido de la factura --}}
            @if(! empty($status_flow))
            <div class="relative mt-6 border-t border-white/10 pt-5">
                <ol class="flex flex-wrap items-center gap-y-3">
                    @foreach($status_flow as $step)
                    <li class="flex items-center">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full ring-2 {{ $step['reached'] ? $step['dot_class'].' ring-white/25' : 'bg-white/10 ring-white/10' }}">
                            @if($step['reached'])
                            <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @else
                            <span class="h-1.5 w-1.5 rounded-full bg-white/40"></span>
                            @endif
                        </span>
                        <span class="ml-2">
                            <span class="block text-xs font-semibold {{ $step['is_current'] ? 'text-white' : ($step['reached'] ? 'text-slate-300' : 'text-slate-500') }}">
                                {{ $step['label'] }}
                                @if($step['is_current'])
                                <span class="ml-1 rounded-full bg-white/15 px-1.5 py-0.5 text-[10px] font-medium text-indigo-100">actual</span>
                                @endif
                            </span>
                            @if($step['hint'])
                            <span class="block text-[10px] text-slate-500">{{ $step['hint'] }}</span>
                            @endif
                        </span>
                        @if(! $loop->last)
                        <span class="mx-3 hidden h-px w-10 sm:block {{ $step['reached'] ? 'bg-white/30' : 'bg-white/10' }}" aria-hidden="true"></span>
                        @endif
                    </li>
                    @endforeach
                </ol>
            </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-6">
            <a href="{{ route('admin.workshop.invoices.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
            <a href="{{ route('admin.workshop.invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir / PDF
            </a>
            @if($invoice->workOrder)
            <a href="{{ route('admin.workshop.work-orders.show', $invoice->work_order_id) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Ver OT {{ $invoice->workOrder->reference }}
            </a>
            @endif

            @if($can_register_payment)
            <button type="button" wire:click="openPaymentModal" class="btn btn-success btn-sm flex-1 justify-center sm:flex-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Registrar pago
            </button>
            @endif

            @if($can_void)
            {{-- Si la DIAN ya validó el documento no se puede anular: el botón
                 queda visible pero deshabilitado, explicando por qué. Esconderlo
                 dejaría a quien lo busca preguntándose dónde está. --}}
            <button type="button" wire:click="openVoid"
                @disabled($void_blocked_reason !== null)
                @if($void_blocked_reason) title="{{ $void_blocked_reason }}" @endif
                class="btn btn-danger btn-sm flex-1 justify-center disabled:cursor-not-allowed disabled:opacity-50 sm:flex-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Anular factura
            </button>
            @endif
        </div>
    </div>

    {{-- Resumen rápido --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 9v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Estado de cobro</p>
            </div>
            <p class="mt-3 text-lg font-bold text-slate-900">{{ $invoice->status_label }}</p>
            <p class="mt-0.5 text-xs text-slate-500">
                @if($invoice->paid_at)
                    Pagada el {{ $invoice->paid_at->format('d/m/Y') }}
                    @if($invoice->payment_method) · {{ $invoice->payment_method }} @endif
                @elseif($due_state['note'])
                    {{ $due_state['note'] }}
                @else
                    Sin fecha de vencimiento registrada.
                @endif
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Estado ante la DIAN</p>
            </div>
            @if($electronic_invoice)
            <p class="mt-3 text-lg font-bold text-slate-900">{{ $electronic_invoice->status->label() }}</p>
            <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $electronic_invoice->document_number }}</p>
            @elseif($dian_applies)
            <p class="mt-3 text-lg font-bold text-slate-400">Sin emitir</p>
            <p class="mt-0.5 text-xs text-slate-500">Puedes enviarla desde el panel de abajo.</p>
            @else
            <p class="mt-3 text-lg font-bold text-slate-300">No aplica</p>
            <p class="mt-0.5 text-xs text-slate-500">Este negocio no factura electrónicamente.</p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Ítems facturados</p>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">{{ $items_summary['lines'] }}</p>
            <p class="mt-0.5 text-xs text-slate-500">
                {{ $items_summary['lines'] === 1 ? 'línea' : 'líneas' }} · {{ $format_qty($items_summary['total']) }} unidades
                @if($items_summary['canceled'] > 0)
                <span class="text-rose-600">· {{ $format_qty($items_summary['canceled']) }} canceladas</span>
                @endif
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-6 4h6m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                </span>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Desglose</p>
            </div>
            <dl class="mt-3 space-y-1 text-xs">
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500">Subtotal</dt>
                    <dd class="font-medium text-slate-900">{{ col_money($invoice->subtotal) }}</dd>
                </div>
                @if((float) $invoice->discount_amount > 0)
                <div class="flex justify-between gap-2">
                    <dt class="text-emerald-700">Descuento{{ $invoice->coupon_code ? ' ('.$invoice->coupon_code.')' : '' }}</dt>
                    <dd class="font-medium text-emerald-700">−{{ col_money($invoice->discount_amount) }}</dd>
                </div>
                @endif
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500">Impuesto ({{ $format_qty($invoice->tax_percentage) }}%)</dt>
                    <dd class="font-medium text-slate-900">{{ col_money($invoice->tax_amount) }}</dd>
                </div>
                <div class="flex justify-between gap-2 border-t border-slate-100 pt-1">
                    <dt class="font-semibold text-slate-700">Total</dt>
                    <dd class="font-bold text-indigo-700">{{ col_money($invoice->total) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Ítems facturados --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
                    <h2 class="font-semibold text-slate-900">Ítems facturados</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Tomados de la orden de trabajo al momento de facturar.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/40">
                            <tr>
                                <th class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-5">#</th>
                                <th class="hidden px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:table-cell sm:px-5">Equipo</th>
                                <th class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-5">Descripción</th>
                                <th class="hidden px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500 md:table-cell md:px-5">Tipo</th>
                                <th class="px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-5">Cant.</th>
                                <th class="hidden px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:table-cell sm:px-5">Complet.</th>
                                <th class="hidden px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500 lg:table-cell lg:px-5">Cancel.</th>
                                <th class="hidden px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500 md:table-cell md:px-5">V. unit.</th>
                                <th class="px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-5">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($invoice->items as $index => $invoiceItem)
                            @php $item = $invoiceItem->workOrderItem; @endphp
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-3 py-3.5 text-slate-400 sm:px-5">{{ $index + 1 }}</td>
                                <td class="hidden whitespace-nowrap px-3 py-3.5 text-xs text-slate-600 sm:table-cell sm:px-5">
                                    {{ $item?->equipment?->select_label ?? $item?->equipment?->plate ?? '—' }}
                                </td>
                                <td class="px-3 py-3.5 text-slate-900 sm:px-5">
                                    <p class="font-medium">{{ $item?->description ?? '—' }}</p>
                                    @if($item?->technician_notes)
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $item->technician_notes }}</p>
                                    @endif
                                </td>
                                <td class="hidden whitespace-nowrap px-3 py-3.5 md:table-cell md:px-5">
                                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                        {{ $item?->productType?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-right tabular-nums text-slate-900 sm:px-5">{{ $invoiceItem->quantity + 0 }}</td>
                                <td class="hidden whitespace-nowrap px-3 py-3.5 text-right sm:table-cell sm:px-5">
                                    <span class="inline-flex min-w-[2rem] items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums {{ (float) $invoiceItem->quantity_complete > 0 ? 'bg-emerald-50 text-emerald-700' : 'text-slate-300' }}">
                                        {{ $invoiceItem->quantity_complete + 0 }}
                                    </span>
                                </td>
                                <td class="hidden whitespace-nowrap px-3 py-3.5 text-right lg:table-cell lg:px-5">
                                    <span class="inline-flex min-w-[2rem] items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums {{ (float) $invoiceItem->quantity_canceled > 0 ? 'bg-rose-50 text-rose-600' : 'text-slate-300' }}">
                                        {{ $invoiceItem->quantity_canceled + 0 }}
                                    </span>
                                </td>
                                <td class="hidden whitespace-nowrap px-3 py-3.5 text-right tabular-nums text-slate-600 md:table-cell md:px-5">{{ $item ? col_money($item->unit_price) : '—' }}</td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-right font-semibold tabular-nums text-slate-900 sm:px-5">{{ $item ? col_money($item->subtotal) : '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center">
                                    <svg class="mx-auto h-10 w-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <p class="mt-2 text-sm text-slate-400">Sin ítems en esta factura.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($invoice->items->isNotEmpty())
                        <tfoot class="border-t border-slate-200 bg-slate-50/60">
                            <tr>
                                <td colspan="8" class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 sm:px-5">Subtotal</td>
                                <td class="px-3 py-2 text-right font-semibold tabular-nums text-slate-700 sm:px-5">{{ col_money($invoice->subtotal) }}</td>
                            </tr>
                            @if((float) $invoice->discount_amount > 0)
                            <tr>
                                <td colspan="8" class="px-3 py-1 text-right text-xs font-semibold uppercase text-emerald-700 sm:px-5">
                                    Descuento{{ $invoice->coupon_code ? ' ('.$invoice->coupon_code.')' : '' }}
                                </td>
                                <td class="px-3 py-1 text-right font-semibold tabular-nums text-emerald-700 sm:px-5">−{{ col_money($invoice->discount_amount) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="8" class="px-3 py-1 text-right text-xs font-semibold uppercase text-slate-500 sm:px-5">
                                    Impuesto ({{ $format_qty($invoice->tax_percentage) }}%)
                                </td>
                                <td class="px-3 py-1 text-right font-semibold tabular-nums text-slate-700 sm:px-5">{{ col_money($invoice->tax_amount) }}</td>
                            </tr>
                            <tr>
                                <td colspan="8" class="px-3 py-2.5 text-right text-sm font-bold uppercase text-slate-900 sm:px-5">Total</td>
                                <td class="px-3 py-2.5 text-right text-base font-bold tabular-nums text-indigo-700 sm:px-5">{{ col_money($invoice->total) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </section>

            @if($invoice->notes)
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
                    <h2 class="font-semibold text-slate-900">Notas</h2>
                </div>
                <p class="px-4 py-4 text-sm leading-relaxed text-slate-700 sm:px-5">{{ $invoice->notes }}</p>
            </section>
            @endif
        </div>

        <div class="space-y-6">
            {{-- Orden de trabajo de origen --}}
            @if($invoice->workOrder)
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <h3 class="font-semibold text-slate-900">Orden de trabajo</h3>
                    <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $invoice->workOrder->status instanceof \App\Enums\WorkOrderStatus ? $invoice->workOrder->status->badgeClass() : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20' }}">
                        {{ $invoice->workOrder->status_label }}
                    </span>
                </div>
                <div class="space-y-3 p-4 sm:p-5">
                    <div>
                        <p class="font-mono text-lg font-bold text-slate-900">{{ $invoice->workOrder->reference }}</p>
                        <p class="mt-0.5 text-sm text-slate-600">{{ $invoice->workOrder->client?->name ?? '—' }}</p>
                    </div>

                    @if($invoice->workOrder->equipments->isNotEmpty())
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            {{ $invoice->workOrder->equipments->count() > 1 ? 'Equipos' : 'Equipo' }}
                        </p>
                        <p class="mt-0.5 text-sm text-slate-700">{{ $invoice->workOrder->equipments->map(fn ($e) => $e->select_label)->join(', ') }}</p>
                    </div>
                    @endif

                    @if($invoice->workOrder->diagnosis)
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Diagnóstico</p>
                        <p class="mt-0.5 line-clamp-3 text-sm text-slate-600">{{ $invoice->workOrder->diagnosis }}</p>
                    </div>
                    @endif

                    <a href="{{ route('admin.workshop.work-orders.show', $invoice->work_order_id) }}" wire:navigate
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Abrir la orden de trabajo
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </section>
            @endif

            {{-- Datos de la factura --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <h3 class="font-semibold text-slate-900">Datos de la factura</h3>
                </div>
                <dl class="divide-y divide-slate-100 px-4 py-2 sm:px-5">
                    @foreach([
                        ['Referencia', $invoice->reference, true],
                        ['A nombre de', $invoice->bill_to_final_consumer
                            ? config('dian.final_consumer.name')
                            : ($invoice->workOrder?->client?->name ?? '—'), false],
                        ['Estado de cobro', $invoice->status_label, false],
                        ['Vencimiento', $invoice->due_date?->format('d/m/Y') ?? '—', false],
                        ['Creada por', $invoice->createdBy?->name ?? '—', false],
                        ['Fecha de pago', $invoice->paid_at?->format('d/m/Y H:i') ?? '—', false],
                        ['Método de pago', $invoice->payment_method ?? '—', false],
                        ['Ref. de pago', $invoice->payment_reference ?? '—', true],
                    ] as [$label, $value, $mono])
                    <div class="flex items-start justify-between gap-3 py-3">
                        <dt class="text-xs font-medium text-slate-500">{{ $label }}</dt>
                        <dd class="text-right text-sm text-slate-900 {{ $mono ? 'font-mono' : '' }}">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </section>

            {{-- Línea de tiempo de la factura --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <div>
                        <h3 class="font-semibold text-slate-900">Línea de tiempo</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Cobro y emisión ante la DIAN, en orden.</p>
                    </div>
                    @if(! empty($status_timeline))
                    <span class="inline-flex shrink-0 items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                        {{ count($status_timeline) }}
                    </span>
                    @endif
                </div>

                @if(empty($status_timeline))
                <div class="px-4 py-8 text-center sm:px-5">
                    <svg class="mx-auto h-9 w-9 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="mt-2 text-sm text-slate-400">Todavía no hay movimientos registrados.</p>
                </div>
                @else
                <ol class="max-h-[28rem] overflow-y-auto p-4 sm:p-5">
                    @foreach($status_timeline as $entry)
                    <li class="relative flex gap-3 {{ $loop->last ? '' : 'pb-5' }}">
                        @if(! $loop->last)
                        <span class="absolute bottom-0 left-[11px] top-7 w-px bg-slate-200" aria-hidden="true"></span>
                        @endif

                        <span class="relative z-10 mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full ring-4 ring-white {{ $entry['dot_class'] }}">
                            @if($entry['is_emission'])
                            <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg>
                            @else
                            <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v-1"/></svg>
                            @endif
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <p class="text-sm font-semibold text-slate-900">
                                    @if($entry['is_opening'])
                                        {{ $entry['to_label'] }}
                                    @else
                                        <span class="font-medium text-slate-500">{{ $entry['from_label'] }}</span>
                                        <span class="mx-1 text-slate-300">→</span>
                                        {{ $entry['to_label'] }}
                                    @endif
                                </p>
                                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold {{ $entry['is_emission'] ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $entry['is_emission'] ? 'DIAN' : 'Cobro' }}
                                </span>
                            </div>

                            <p class="mt-0.5 text-[11px] text-slate-400" @if($entry['changed_ago']) title="{{ $entry['changed_ago'] }}" @endif>
                                {{ $entry['changed_at'] }}
                                @if($entry['user_name']) · {{ $entry['user_name'] }} @endif
                                @if($entry['document_number']) · <span class="font-mono">{{ $entry['document_number'] }}</span> @endif
                            </p>

                            @if($entry['duration'] && $entry['from_label'])
                            <p class="mt-1 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $entry['duration'] }} en «{{ $entry['from_label'] }}»
                            </p>
                            @endif

                            @if($entry['comment'])
                            <p class="mt-1.5 rounded-lg border-l-2 border-slate-300 bg-slate-50 px-2.5 py-1.5 text-xs leading-relaxed text-slate-600">
                                {{ $entry['comment'] }}
                            </p>
                            @endif

                            @if($entry['is_backfilled'])
                            <p class="mt-1 text-[10px] text-slate-300" title="Reconstruido a partir de las fechas guardadas antes de existir esta bitácora">
                                registro reconstruido
                            </p>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ol>
                @endif
            </section>
        </div>
    </div>

    <div id="inv-dian" class="scroll-mt-6">
        <livewire:admin.workshop.invoices.dian-panel :invoice="$invoice" :key="'dian-panel-'.$invoice->id" />
    </div>

    @if($showPaymentModal)
    <x-ui.modal centered maxWidth="md">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closePaymentModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">Registrar pago</h3>
            <button type="button" wire:click="closePaymentModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="savePayment" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <dl class="space-y-2 rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Factura</dt>
                        <dd class="font-mono font-semibold text-slate-900">{{ $invoice->reference }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Cliente</dt>
                        <dd class="text-right font-medium text-slate-900">{{ $invoice->workOrder?->client?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-slate-200 pt-2">
                        <dt class="font-medium text-slate-700">Monto a registrar</dt>
                        <dd class="text-base font-bold text-emerald-700">{{ col_money($invoice->total) }}</dd>
                    </div>
                </dl>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Método de pago <span class="text-rose-500">*</span></label>
                    <select wire:model="business_payment_method_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('business_payment_method_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar…</option>
                        @forelse($payment_methods as $method)
                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @empty
                        <option value="" disabled>No hay métodos de pago activos</option>
                        @endforelse
                    </select>
                    @error('business_payment_method_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    @if($payment_methods->isEmpty())
                    <p class="mt-1 text-xs text-amber-700">Configura al menos un método de pago para el negocio antes de registrar cobros.</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Fecha del pago <span class="text-rose-500">*</span></label>
                        <input type="date" wire:model="paid_at" max="{{ now()->toDateString() }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('paid_at') border-rose-400 bg-rose-50 @enderror">
                        @error('paid_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Referencia</label>
                        <input type="text" wire:model="payment_reference" placeholder="N.º de transferencia, voucher…"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('payment_reference') border-rose-400 bg-rose-50 @enderror">
                        @error('payment_reference') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Nota</label>
                    <textarea wire:model="payment_notes" rows="3" placeholder="Opcional: observación del cobro…"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('payment_notes') border-rose-400 bg-rose-50 @enderror"></textarea>
                    @error('payment_notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <p class="text-xs text-slate-500">
                    La factura quedará como pagada y el movimiento se registrará en su línea de tiempo.
                </p>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closePaymentModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="savePayment" @disabled($payment_methods->isEmpty())
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                    <span wire:loading.remove wire:target="savePayment">Registrar pago</span>
                    <span wire:loading wire:target="savePayment">Registrando…</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif

    @if($showVoidModal)
    <x-ui.modal centered maxWidth="md">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeVoid"></div>
        </x-slot:backdrop>

        <form wire:submit="voidInvoice" class="flex min-h-0 flex-col">
            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
                <h3 class="text-base font-semibold text-slate-900">Anular factura {{ $invoice->reference }}</h3>
                <button type="button" wire:click="closeVoid" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-3">
                    <p class="text-sm font-semibold text-rose-900">Esto también cancela la orden de trabajo</p>
                    <p class="mt-1 text-xs text-rose-800">
                        La OT {{ $invoice->workOrder?->reference }} quedará cancelada junto con la factura.
                        @if($electronic_invoice && $electronic_invoice->transaction_id)
                            Además se retirará el documento {{ $electronic_invoice->document_number }} del proveedor,
                            que todavía no ha sido validado por la DIAN.
                        @endif
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">
                        Motivo de la anulación <span class="text-rose-500">*</span>
                    </label>
                    <textarea wire:model="void_reason" rows="3" placeholder="Por qué se anula esta factura"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('void_reason') border-rose-400 bg-rose-50 @enderror"></textarea>
                    @error('void_reason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-500">Queda en la línea de tiempo de la factura y en la bitácora.</p>
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closeVoid" class="btn btn-outline-secondary btn-sm">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="btn btn-danger btn-sm">
                    <span wire:loading.remove wire:target="voidInvoice">Anular factura</span>
                    <span wire:loading wire:target="voidInvoice">Anulando...</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
