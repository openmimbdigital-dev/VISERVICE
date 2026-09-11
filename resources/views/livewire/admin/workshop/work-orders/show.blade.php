@php
    $format_qty = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
@endphp

<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.work-orders.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Órdenes de Trabajo</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $workOrder->reference }}</span>
    </nav>

    {{-- Encabezado: identidad de la OT, total, ciclo de vida y acciones --}}
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 px-4 py-6 sm:px-6 sm:py-7">
            <div class="pointer-events-none absolute -right-20 -top-24 h-60 w-60 rounded-full bg-indigo-500/20 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-28 left-1/3 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl" aria-hidden="true"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-300/80">Taller · Orden de trabajo</p>

                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <h1 class="font-mono text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $workOrder->reference }}</h1>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $status_badge_class }}">
                            {{ $workOrder->status_label }}
                        </span>
                        @if($workOrder->quotation_id)
                        <a href="{{ route('admin.workshop.quotations.show', $workOrder->quotation_id) }}" wire:navigate
                            class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-xs font-medium text-indigo-100 ring-1 ring-white/15 transition hover:bg-white/20">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Desde cotización
                        </a>
                        @endif
                    </div>

                    <dl class="mt-5 flex flex-wrap gap-x-8 gap-y-4">
                        <div class="min-w-0">
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Cliente</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-white">{{ $workOrder->client?->name ?? '—' }}</dd>
                            @if($workOrder->bill_to_final_consumer)
                            <dd class="mt-1 inline-flex items-center gap-1 rounded-full bg-amber-400/15 px-2 py-0.5 text-[10px] font-semibold text-amber-200 ring-1 ring-amber-400/25"
                                title="La factura sale a nombre de {{ config('dian.final_consumer.name') }} (NIT {{ config('dian.final_consumer.document_number') }})">
                                Factura a consumidor final
                            </dd>
                            @endif
                        </div>
                        @if($workOrder->equipments->isNotEmpty())
                        <div class="min-w-0">
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                {{ $workOrder->equipments->count() > 1 ? 'Equipos' : 'Equipo' }}
                            </dt>
                            <dd class="mt-0.5 text-sm text-slate-200">{{ $workOrder->equipments->map(fn ($e) => $e->select_label)->join(', ') }}</dd>
                        </div>
                        @endif
                        @if($workOrder->estimated_delivery)
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Entrega estimada</dt>
                            <dd class="mt-0.5 text-sm text-slate-200">{{ $workOrder->estimated_delivery->format('d/m/Y') }}</dd>
                        </div>
                        @endif
                        @if($lifecycle['elapsed'])
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                {{ $lifecycle['is_open'] ? 'En taller' : 'Duración total' }}
                            </dt>
                            <dd class="mt-0.5 text-sm text-slate-200">
                                {{ $lifecycle['elapsed'] }}
                                @if($lifecycle['started_at'])
                                <span class="text-slate-400">· desde {{ $lifecycle['started_at'] }}</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                    </dl>

                    @if($workOrder->diagnosis)
                    <p class="mt-5 max-w-2xl rounded-xl bg-white/5 px-3.5 py-2.5 text-sm leading-relaxed text-slate-300 ring-1 ring-white/10">
                        <span class="font-semibold text-slate-100">Diagnóstico:</span> {{ $workOrder->diagnosis }}
                    </p>
                    @endif
                </div>

                <div class="shrink-0 rounded-2xl bg-white/5 px-5 py-4 ring-1 ring-white/10 lg:min-w-[16rem] lg:text-right">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total de la OT</p>
                    <p class="mt-1 text-3xl font-bold tracking-tight text-white">{{ col_money($workOrder->total) }}</p>
                    @if((float) $workOrder->advance_amount > 0)
                    <div class="mt-3 border-t border-white/10 pt-3">
                        <p class="text-xs text-amber-200">
                            Anticipo acordado ({{ $format_qty($workOrder->advance_percentage) }}%):
                            <span class="font-semibold text-amber-100">{{ col_money($workOrder->advance_amount) }}</span>
                        </p>
                        @can('workshop.advance-payments.view')
                        <a href="{{ route('admin.workshop.advance-payments.show', $workOrder) }}" wire:navigate
                            class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-indigo-200 transition hover:text-white lg:justify-end">
                            Gestionar abonos
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        @endcan
                    </div>
                    @endif
                </div>
            </div>

            {{-- Ciclo de vida: por dónde va la OT --}}
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
                        <span class="ml-2 text-xs font-semibold {{ $step['is_current'] ? 'text-white' : ($step['reached'] ? 'text-slate-300' : 'text-slate-500') }}">
                            {{ $step['label'] }}
                            @if($step['is_current'])
                            <span class="ml-1 rounded-full bg-white/15 px-1.5 py-0.5 text-[10px] font-medium text-indigo-100">actual</span>
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

        {{-- Barra de acciones --}}
        @if(! $workOrder->isDraft())
        <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-6">
            <a href="{{ route('admin.workshop.work-orders.print', $workOrder) }}" target="_blank"
                class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir / PDF
            </a>

            @if($can_create_remission)
            <a href="{{ route('admin.workshop.remissions.form', ['work_order' => $workOrder->id]) }}" wire:navigate
                class="btn btn-success btn-sm flex-1 justify-center sm:flex-none">
                Crear remisión
            </a>
            @elseif($linked_remission)
            <a href="{{ route('admin.workshop.remissions.show', $linked_remission) }}" wire:navigate
                class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                Ver remisión {{ $linked_remission->reference }}
            </a>
            @endif

            @if($can_invoice)
            <button type="button" wire:click="invoiceWorkOrder" wire:loading.attr="disabled" wire:target="invoiceWorkOrder"
                title="{{ $dian_offer_on_invoice ? 'Se te preguntará si además la emites ante la DIAN' : 'Genera la factura de esta OT' }}"
                class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none disabled:opacity-60">
                <span wire:loading.remove wire:target="invoiceWorkOrder">Facturar{{ $dian_offer_on_invoice ? '…' : '' }}</span>
                <span wire:loading wire:target="invoiceWorkOrder">Facturando…</span>
            </button>
            @elseif($latest_invoice)
            @can('workshop.invoices.view')
            <a href="{{ route('admin.workshop.invoices.show', $latest_invoice) }}" wire:navigate
                class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                Factura {{ $latest_invoice->reference }}
            </a>
            @else
            <span class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 sm:flex-none"
                title="Factura generada">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Factura {{ $latest_invoice->reference }}
            </span>
            @endcan
            @endif

            @if($electronic_invoice)
            <a href="#wo-dian" x-on:click.prevent="document.getElementById('wo-dian')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition hover:opacity-80 sm:flex-none {{ $electronic_invoice->status->badgeClass() }}"
                title="{{ $electronic_invoice->document_number }} — ver detalle DIAN">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                DIAN: {{ $electronic_invoice->status->label() }}
            </a>
            @endif

            @if($can_edit)
                @if($can_manage_documents)
                <button type="button" wire:click="openDocumentModal" title="Asociar documento"
                    class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                    Documento asociado
                </button>
                @else
                <button type="button" disabled title="{{ $documents_disabled_title }}"
                    class="btn btn-outline-secondary btn-sm flex-1 justify-center opacity-50 sm:flex-none">
                    Documento asociado
                </button>
                @endif

                @if($edit_disabled)
                <button type="button" disabled title="{{ $edit_disabled_title }}"
                    class="btn btn-outline-secondary btn-sm flex-1 justify-center opacity-50 sm:flex-none">
                    Editar
                </button>
                @else
                <a href="{{ route('admin.workshop.work-orders.form.edit', $workOrder) }}" wire:navigate
                    class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                    Editar
                </a>
                @endif
            @endif
        </div>
        @endif

        @if($edit_disabled)
        <p class="flex items-start gap-2 border-t border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800 sm:px-6">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>Esta OT está {{ strtolower($workOrder->status_label) }}; no se puede editar ni cambiar de estado.</span>
        </p>
        @endif
    </div>

    @if(! $workOrder->isComplete())
    <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
        <p>Esta orden de trabajo está incompleta (paso {{ $workOrder->step }} de {{ $workOrder->final_step }}, {{ $workOrder->progressPercent() }}%).</p>
        @if($can_edit && ! $edit_disabled)
        <a href="{{ route('admin.workshop.work-orders.form.edit', $workOrder) }}" wire:navigate class="font-semibold text-amber-800 underline underline-offset-2">Continuar registro</a>
        @endif
    </div>
    @endif

    @if($workOrder->isComplete())
    {{-- Resumen rápido --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Ítems</p>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">{{ $items_summary['lines'] }}</p>
            <p class="mt-0.5 text-xs text-slate-500">
                {{ $items_summary['lines'] === 1 ? 'línea' : 'líneas' }} · {{ $format_qty($items_summary['total']) }} unidades
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </span>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Avance del trabajo</p>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">{{ $items_summary['percent'] }}<span class="text-base font-semibold text-slate-400">%</span></p>
            @if($items_summary['total'] > 0)
            <div class="mt-2 flex h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="bg-emerald-500" style="width: {{ ($items_summary['complete'] / $items_summary['total']) * 100 }}%"></div>
                <div class="bg-rose-400" style="width: {{ ($items_summary['canceled'] / $items_summary['total']) * 100 }}%"></div>
            </div>
            @endif
            <p class="mt-1.5 text-xs text-slate-500">
                {{ $format_qty($items_summary['complete']) }} completados ·
                {{ $format_qty($items_summary['pending']) }} pendientes
                @if($items_summary['canceled'] > 0)
                · {{ $format_qty($items_summary['canceled']) }} cancelados
                @endif
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 9v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Anticipo</p>
            </div>
            @if((float) $workOrder->advance_amount > 0)
            <p class="mt-3 text-2xl font-bold text-slate-900">{{ col_money($workOrder->advancePaidAmount()) }}</p>
            <p class="mt-0.5 text-xs text-slate-500">
                de {{ col_money($workOrder->advance_amount) }} acordado
            </p>
            @if($workOrder->advanceRemainingAmount() > 0)
            <p class="mt-1 text-xs font-semibold text-amber-700">Saldo por abonar: {{ col_money($workOrder->advanceRemainingAmount()) }}</p>
            @else
            <p class="mt-1 text-xs font-semibold text-emerald-700">Anticipo cubierto</p>
            @endif
            @else
            <p class="mt-3 text-2xl font-bold text-slate-300">—</p>
            <p class="mt-0.5 text-xs text-slate-500">Esta OT no tiene anticipo acordado.</p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Documentos</p>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">{{ $workOrder->associatedDocuments->count() }}</p>
            <p class="mt-0.5 text-xs text-slate-500">asociados a la OT</p>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @if($electronic_invoice)
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $electronic_invoice->status->badgeClass() }}"
                    title="{{ $electronic_invoice->document_number }}">
                    DIAN: {{ $electronic_invoice->status->label() }}
                </span>
                @endif
                @if($latest_invoice)
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Facturada</span>
                @endif
                @if($linked_remission)
                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Con remisión</span>
                @endif
                @if($workOrder->quotation_id)
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">Desde cotización</span>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Ítems de trabajo --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div>
                        <h2 class="font-semibold text-slate-900">Ítems de trabajo</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Repuestos y servicios incluidos en esta orden.</p>
                    </div>
                    @if($can_manage)
                    <button type="button"
                        x-on:click="document.getElementById('wo-catalog')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        class="btn btn-primary btn-sm w-full justify-center sm:w-auto">
                        + Agregar ítem
                    </button>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/40">
                            <tr>
                                <th class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-4">Equipo</th>
                                <th class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-4">Tipo</th>
                                <th class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-4">Descripción</th>
                                <th class="hidden px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:table-cell sm:px-4">Cant.</th>
                                <th class="hidden px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500 md:table-cell sm:px-4">P. Unit.</th>
                                <th class="px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-4">Subtotal</th>
                                <th class="px-3 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-4">Completados</th>
                                <th class="px-3 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-4">Cancelados</th>
                                @if($can_manage)
                                <th class="px-3 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-500 sm:px-4">Avance</th>
                                <th class="px-3 py-2.5 sm:px-4"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($workOrder->items as $item)
                            @php
                                $item_qty = (float) $item->quantity;
                                $item_complete = (float) $item->quantity_complete;
                                $item_canceled = (float) $item->quantity_canceled;
                                $can_complete = $item_complete < $item_qty;
                                $can_cancel = $item_canceled < $item_qty;
                                $complete_pct = $item_qty > 0 ? ($item_complete / $item_qty) * 100 : 0;
                                $canceled_pct = $item_qty > 0 ? ($item_canceled / $item_qty) * 100 : 0;
                            @endphp
                            <tr wire:key="woi-{{ $item->id }}" class="transition hover:bg-slate-50/70">
                                <td class="px-3 py-3 text-xs text-slate-600 sm:px-4">{{ $item->equipment?->select_label ?? '—' }}</td>
                                <td class="px-3 py-3 sm:px-4">
                                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                        {{ $item->productType?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-sm text-slate-900 sm:px-4">
                                    <div class="flex items-center gap-2.5">
                                        @if($item->catalogProduct)
                                        <x-ui.product-image :product="$item->catalogProduct" size="sm" />
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium">{{ $item->description }}</p>
                                            @if($item->technician_notes)
                                            <p class="mt-0.5 text-xs text-slate-400">{{ $item->technician_notes }}</p>
                                            @endif
                                            @if($item_qty > 0 && ($item_complete > 0 || $item_canceled > 0))
                                            <div class="mt-1.5 flex h-1 w-full max-w-[10rem] overflow-hidden rounded-full bg-slate-100"
                                                title="{{ $format_qty($item_complete) }} completados · {{ $format_qty($item_canceled) }} cancelados de {{ $format_qty($item_qty) }}">
                                                <div class="bg-emerald-500" style="width: {{ $complete_pct }}%"></div>
                                                <div class="bg-rose-400" style="width: {{ $canceled_pct }}%"></div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden px-3 py-3 text-right text-sm text-slate-600 sm:table-cell sm:px-4">{{ $item->quantity }}</td>
                                <td class="hidden px-3 py-3 text-right text-sm text-slate-600 md:table-cell sm:px-4">
                                    {{ col_money($item->unit_price) }}
                                    @if($item->hasDiscount())
                                    <span class="mt-0.5 block text-[10px] font-semibold text-amber-700" title="Descuento del producto: −{{ col_money($item->discountAmount()) }}">
                                        −{{ $item->discountLabel() }} descuento
                                    </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right font-semibold text-slate-900 sm:px-4">{{ col_money($item->subtotal) }}</td>
                                <td class="px-3 py-3 text-center sm:px-4">
                                    <span class="inline-flex min-w-[2rem] items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $item_complete > 0 ? 'bg-emerald-50 text-emerald-700' : 'text-slate-300' }}">
                                        {{ $item->quantity_complete + 0 }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center sm:px-4">
                                    <span class="inline-flex min-w-[2rem] items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $item_canceled > 0 ? 'bg-rose-50 text-rose-600' : 'text-slate-300' }}">
                                        {{ $item->quantity_canceled + 0 }}
                                    </span>
                                </td>
                                @if($can_manage)
                                <td class="px-3 py-3 sm:px-4">
                                    <div class="flex flex-wrap items-center justify-center gap-1">
                                        <button type="button"
                                            wire:click="completeItemQuantity({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="completeItemQuantity({{ $item->id }})"
                                            @disabled(! $can_complete)
                                            class="inline-flex items-center justify-center rounded-lg bg-emerald-50 p-1.5 text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-40"
                                            title="Completar +1">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <button type="button"
                                            wire:click="cancelItemQuantity({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="cancelItemQuantity({{ $item->id }})"
                                            @disabled(! $can_cancel)
                                            class="inline-flex items-center justify-center rounded-lg bg-rose-50 p-1.5 text-rose-700 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-40"
                                            title="Cancelar +1">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-3 py-3 sm:px-4">
                                    <div class="flex flex-wrap justify-end gap-1">
                                        <button wire:click="openEditItem({{ $item->id }})" type="button" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Editar">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="deleteItem({{ $item->id }})" type="button" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600" title="Eliminar">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $can_manage ? 10 : 8 }}" class="px-4 py-12 text-center">
                                    <svg class="mx-auto h-10 w-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    <p class="mt-2 text-sm text-slate-400">Sin ítems. Usa «Agregar ítem» para ir al catálogo.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($workOrder->items->isNotEmpty())
                        <tfoot class="border-t border-slate-200 bg-slate-50/60">
                            <tr>
                                <td colspan="5" class="hidden px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 md:table-cell">Subtotal</td>
                                <td colspan="3" class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 md:hidden">Subtotal</td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-700">{{ col_money($workOrder->subtotal) }}</td>
                                <td colspan="{{ $can_manage ? 4 : 2 }}"></td>
                            </tr>
                            @if((float) $workOrder->discount_amount > 0)
                            <tr>
                                <td colspan="5" class="hidden px-4 py-1 text-right text-xs font-semibold uppercase text-emerald-700 md:table-cell">Cupón {{ $workOrder->coupon_code }}</td>
                                <td colspan="3" class="px-4 py-1 text-right text-xs font-semibold uppercase text-emerald-700 md:hidden">Cupón {{ $workOrder->coupon_code }}</td>
                                <td class="px-4 py-1 text-right font-semibold text-emerald-700">−{{ col_money($workOrder->discount_amount) }}</td>
                                <td colspan="{{ $can_manage ? 4 : 2 }}"></td>
                            </tr>
                            @endif
                            @if((float) $workOrder->advance_amount > 0)
                            <tr>
                                <td colspan="5" class="hidden px-4 py-1 text-right text-xs font-semibold uppercase text-amber-700 md:table-cell">Anticipo {{ $workOrder->advance_percentage }}%</td>
                                <td colspan="3" class="px-4 py-1 text-right text-xs font-semibold uppercase text-amber-700 md:hidden">Anticipo {{ $workOrder->advance_percentage }}%</td>
                                <td class="px-4 py-1 text-right font-semibold text-amber-700">{{ col_money($workOrder->advance_amount) }}</td>
                                <td colspan="{{ $can_manage ? 4 : 2 }}"></td>
                            </tr>
                            @endif
                            @forelse($workOrder->appliedTaxes as $tax)
                            <tr>
                                <td colspan="5" class="hidden px-4 py-1 text-right text-xs font-semibold uppercase text-slate-500 md:table-cell">{{ $tax->custom_tax_name }} {{ $tax->percentageLabel() }}%</td>
                                <td colspan="3" class="px-4 py-1 text-right text-xs font-semibold uppercase text-slate-500 md:hidden">{{ $tax->custom_tax_name }} {{ $tax->percentageLabel() }}%</td>
                                <td class="px-4 py-1 text-right font-semibold text-slate-700">{{ col_money($tax->tax_amount) }}</td>
                                <td colspan="{{ $can_manage ? 4 : 2 }}"></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="hidden px-4 py-1 text-right text-xs font-semibold uppercase text-slate-500 md:table-cell">Impuestos</td>
                                <td colspan="3" class="px-4 py-1 text-right text-xs font-semibold uppercase text-slate-500 md:hidden">Impuestos</td>
                                <td class="px-4 py-1 text-right font-semibold text-slate-700">{{ col_money(0) }}</td>
                                <td colspan="{{ $can_manage ? 4 : 2 }}"></td>
                            </tr>
                            @endforelse
                            <tr>
                                <td colspan="5" class="hidden px-4 py-2.5 text-right text-sm font-bold uppercase text-slate-900 md:table-cell">Total</td>
                                <td colspan="3" class="px-4 py-2.5 text-right text-sm font-bold uppercase text-slate-900 md:hidden">Total</td>
                                <td class="px-4 py-2.5 text-right text-base font-bold text-indigo-700">{{ col_money($workOrder->total) }}</td>
                                <td colspan="{{ $can_manage ? 4 : 2 }}"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </section>

            {{-- Documentos asociados --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div>
                        <h2 class="font-semibold text-slate-900">Documentos asociados</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Referencias externas vinculadas a esta OT.</p>
                    </div>
                    @if($can_edit)
                        @if($can_manage_documents)
                        <button type="button" wire:click="openDocumentModal" class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Agregar</button>
                        @else
                        <span title="{{ $documents_disabled_title }}" class="cursor-not-allowed text-xs font-medium text-slate-300 opacity-50" aria-disabled="true">Agregar</span>
                        @endif
                    @endif
                </div>
                <div class="p-4 sm:p-5">
                    @forelse($workOrder->associatedDocuments as $document)
                    @if($loop->first)
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @endif
                        <div wire:key="wo-doc-{{ $document->id }}"
                            class="flex items-start justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-3 transition hover:border-indigo-200 hover:bg-white">
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $document->name }}</p>
                                <p class="mt-1 break-words font-mono text-sm text-slate-900">{{ $document->value }}</p>
                                @if($document->send_invoice)
                                <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Enviado a facturación
                                </span>
                                @endif
                            </div>
                            @if($can_edit)
                                @if($can_manage_documents)
                                <button type="button" wire:click="openEditAssociatedDocument({{ $document->id }})" title="Editar documento" class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 20h4.586a1 1 0 00.707-.293l9.414-9.414a2 2 0 00-2.828-2.828L6.465 16.88A1 1 0 006.172 17.586V20z"/></svg>
                                </button>
                                @else
                                <span title="{{ $documents_disabled_title }}" class="shrink-0 cursor-not-allowed rounded-lg p-1.5 text-slate-300 opacity-50" aria-disabled="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 20h4.586a1 1 0 00.707-.293l9.414-9.414a2 2 0 00-2.828-2.828L6.465 16.88A1 1 0 006.172 17.586V20z"/></svg>
                                </span>
                                @endif
                            @endif
                        </div>
                    @if($loop->last)
                    </div>
                    @endif
                    @empty
                    <div class="py-6 text-center">
                        <svg class="mx-auto h-9 w-9 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="mt-2 text-sm text-slate-400">Sin documentos asociados.</p>
                    </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            {{-- Estado de la OT --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-slate-900">Estado de la OT</h3>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $status_badge_class }}">
                            {{ $workOrder->status_label }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">
                        @if($can_change_status)
                            Actualiza el seguimiento de la orden de trabajo.
                        @elseif(! $can_edit)
                            No tienes permiso para cambiar el estado de la OT.
                        @else
                            Esta OT está {{ strtolower($workOrder->status_label) }} y ya no admite cambios de estado.
                        @endif
                    </p>
                </div>

                @if($can_change_status)
                <form wire:submit="updateStatus" class="space-y-4 p-4 sm:p-5">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Nuevo estado</label>
                        <select wire:model.live="status"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('status') border-rose-400 bg-rose-50 @enderror">
                            @foreach($status_options as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">
                            Comentario
                            @if($show_cancel_comment_required)
                                <span class="text-rose-500">*</span>
                            @endif
                        </label>
                        <textarea wire:model="status_comment" rows="3"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('status_comment') border-rose-400 bg-rose-50 @enderror"
                            placeholder="{{ $status_comment_placeholder }}"></textarea>
                        @error('status_comment') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                        <span wire:loading.remove wire:target="updateStatus">Guardar estado</span>
                        <span wire:loading wire:target="updateStatus">Guardando…</span>
                    </button>
                </form>
                @else
                <dl class="divide-y divide-slate-100 px-4 py-2 sm:px-5">
                    <div class="flex items-center justify-between gap-3 py-3">
                        <dt class="text-xs font-medium text-slate-500">Estado actual</dt>
                        <dd class="text-sm font-semibold text-slate-900">{{ $workOrder->status_label }}</dd>
                    </div>
                    @if($workOrder->finalized_at)
                    <div class="flex items-center justify-between gap-3 py-3">
                        <dt class="text-xs font-medium text-slate-500">Finalizada el</dt>
                        <dd class="text-sm text-slate-900">{{ $workOrder->finalized_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                    @if($lifecycle['elapsed'])
                    <div class="flex items-center justify-between gap-3 py-3">
                        <dt class="text-xs font-medium text-slate-500">{{ $lifecycle['is_open'] ? 'Tiempo en taller' : 'Duración total' }}</dt>
                        <dd class="text-sm text-slate-900">{{ $lifecycle['elapsed'] }}</dd>
                    </div>
                    @endif
                </dl>
                @endif
            </section>

            {{-- Historial de estados --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <div>
                        <h3 class="font-semibold text-slate-900">Historial de estados</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Cada cambio, con su autor y su comentario.</p>
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
                    <p class="mt-2 text-sm text-slate-400">Todavía no hay cambios de estado registrados.</p>
                </div>
                @else
                <ol class="max-h-[28rem] overflow-y-auto p-4 sm:p-5">
                    @foreach($status_timeline as $entry)
                    <li class="relative flex gap-3 {{ $loop->last ? '' : 'pb-5' }}">
                        @if(! $loop->last)
                        <span class="absolute bottom-0 left-[11px] top-7 w-px bg-slate-200" aria-hidden="true"></span>
                        @endif

                        <span class="relative z-10 mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full ring-4 ring-white {{ $entry['dot_class'] }}">
                            @if($entry['is_opening'])
                            <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            @else
                            <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            @endif
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">
                                @if($entry['is_opening'])
                                    OT abierta como {{ $entry['to_label'] }}
                                @else
                                    <span class="font-medium text-slate-500">{{ $entry['from_label'] }}</span>
                                    <span class="mx-1 text-slate-300">→</span>
                                    {{ $entry['to_label'] }}
                                @endif
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-400" @if($entry['changed_ago']) title="{{ $entry['changed_ago'] }}" @endif>
                                {{ $entry['changed_at'] }}
                                @if($entry['user_name']) · {{ $entry['user_name'] }} @endif
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
                            <p class="mt-1 text-[10px] text-slate-300" title="Reconstruido a partir de la auditoría previa a la bitácora de estados">
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

    {{-- Facturación electrónica: el estado DIAN se ve y se opera desde la propia OT --}}
    @if($can_view_dian_panel && $latest_invoice)
    <div id="wo-dian" class="scroll-mt-6">
        <livewire:admin.workshop.invoices.dian-panel
            :invoice="$latest_invoice"
            :key="'wo-dian-panel-'.$latest_invoice->id" />
    </div>
    @elseif($dian_setting)
    <div id="wo-dian" class="mb-6 scroll-mt-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div>
                <h2 class="font-semibold text-slate-900">Facturación electrónica DIAN</h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    Proveedor: {{ ucfirst($dian_setting->provider) }} —
                    <span class="font-medium">{{ $dian_setting->environment === 'production' ? 'Producción' : 'Pruebas' }}</span>
                </p>
            </div>
            <span class="inline-flex shrink-0 items-center self-start rounded-full px-3 py-1 text-xs font-semibold {{ $dian_missing === [] ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' }}">
                {{ $dian_missing === [] ? 'Lista para emitir' : 'Faltan datos' }}
            </span>
        </div>
        <div class="space-y-4 p-4 sm:p-5">
            @if($dian_missing !== [])
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <p class="text-sm font-semibold text-amber-900">Faltan datos para poder emitir</p>
                <ul class="mt-1.5 list-inside list-disc space-y-0.5 text-xs text-amber-800">
                    @foreach($dian_missing as $item)
                    <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-3">
                <div>
                    <dt class="text-xs font-medium text-slate-500">Resolución</dt>
                    <dd class="font-mono text-sm text-slate-900">{{ $dian_setting->resolution_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500">Prefijo</dt>
                    <dd class="font-mono text-sm text-slate-900">{{ $dian_setting->prefix ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500">Próximo número</dt>
                    <dd class="font-mono text-sm text-slate-900">{{ $dian_setting->prefix }}{{ $dian_setting->upcomingConsecutive() }}</dd>
                </div>
            </dl>

            <p class="text-sm text-slate-500">
                Esta OT todavía no tiene factura.
                @if($dian_missing === [])
                    Al facturarla se te preguntará si quieres emitirla enseguida ante la DIAN.
                @endif
            </p>
        </div>
    </div>
    @endif

    @if($can_manage)
    <section id="wo-catalog" class="scroll-mt-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
            <div>
                <h2 class="font-semibold text-slate-800">Catálogo de productos</h2>
                <p class="mt-0.5 text-xs text-slate-500">Agrega productos a esta OT desde el catálogo.</p>
            </div>

            @if($workOrder->equipments->count() > 1)
            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Equipo activo para nuevos productos</label>
                <select wire:model.live="active_equipment_id" class="w-full max-w-sm rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm @error('active_equipment_id') border-rose-400 @enderror">
                    <option value="">Sin asignar</option>
                    @foreach($workOrder->equipments as $equipment)
                    <option value="{{ $equipment->id }}">{{ $equipment->select_label }}</option>
                    @endforeach
                </select>
                @error('active_equipment_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-slate-500">Los productos que agregues del catálogo se asignarán a este equipo. Déjalo vacío para no asociarlos.</p>
            </div>
            @endif
        </div>

        <div class="border-b border-slate-100 p-4 sm:p-5">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" wire:model.live.debounce.300ms="catalog_search" placeholder="Buscar producto por nombre o SKU..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3.5 text-sm">
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" wire:click="$set('catalog_type_filter', null)"
                    class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ ! $catalog_type_filter ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Todos
                </button>
                @foreach($product_types as $type)
                <button type="button" wire:click="$set('catalog_type_filter', {{ $type->id }})"
                    class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ (int) $catalog_type_filter === $type->id ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $type->name }}
                </button>
                @endforeach
            </div>
        </div>

        <div
            x-on:scroll.throttle.200ms="if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 150 && @js($catalog_has_more)) { $wire.loadMoreCatalogProducts() }"
            wire:loading.class="opacity-50" wire:target="catalog_search,catalog_type_filter"
            class="max-h-[34rem] overflow-y-auto p-4 transition sm:p-5">
            @if($catalog_products->isEmpty())
            <p class="py-10 text-center text-sm text-slate-400">No hay productos que coincidan con la búsqueda.</p>
            @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                @foreach($catalog_products as $product)
                @php $in_cart = (float) ($cart_quantities[$product->id] ?? 0); @endphp
                <div wire:key="show-catalog-product-{{ $product->id }}" class="group relative flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white transition hover:border-indigo-300 hover:shadow-md">
                    @if($product->product_type)
                    <span class="absolute left-2 top-2 z-10 inline-flex items-center rounded-full bg-slate-900/70 px-2 py-0.5 text-[10px] font-semibold text-white shadow backdrop-blur-sm">
                        {{ $product->product_type->name }}
                    </span>
                    @endif
                    @if($product->hasDiscount())
                    <span class="absolute {{ $in_cart > 0 ? 'right-2 top-9' : 'right-2 top-2' }} z-10 inline-flex items-center rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white shadow">
                        −{{ $product->discountLabel() }}
                    </span>
                    @endif
                    @if($in_cart > 0)
                    <span class="absolute right-2 top-2 z-10 inline-flex items-center rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white shadow">
                        En OT: {{ rtrim(rtrim(number_format($in_cart, 2, '.', ''), '0'), '.') }}
                    </span>
                    @endif
                    <button type="button" wire:click="showProductPreview({{ $product->id }})" class="flex flex-1 flex-col text-left">
                        <x-ui.product-image :product="$product" size="xl" class="border-b border-slate-100" />
                        <div class="flex flex-1 flex-col gap-1 p-3">
                            <p class="line-clamp-2 text-sm font-medium leading-snug text-slate-800" title="{{ $product->name }}">{{ $product->name }}</p>
                            <p class="font-mono text-[11px] text-slate-400">{{ $product->sku }}</p>
                            @if($product->hasDiscount())
                            <div class="mt-auto">
                                @if($catalog_apply_discount[$product->id] ?? true)
                                <p class="text-[11px] text-slate-400 line-through">{{ col_money($product->sale_price) }}</p>
                                <p class="text-sm font-semibold text-emerald-700">{{ col_money($product->finalPrice()) }}</p>
                                @else
                                <p class="text-sm font-semibold text-indigo-700">{{ col_money($product->sale_price) }}</p>
                                @endif
                            </div>
                            @else
                            <p class="mt-auto text-sm font-semibold text-indigo-700">{{ col_money($product->sale_price) }}</p>
                            @endif
                        </div>
                    </button>
                    <div class="flex flex-col gap-1.5 border-t border-slate-100 p-2">
                        @if($product->hasDiscount())
                        <label class="flex cursor-pointer items-center gap-1.5 rounded-lg bg-amber-50 px-2 py-1">
                            <input type="checkbox" wire:model.live="catalog_apply_discount.{{ $product->id }}"
                                class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-[11px] font-medium text-amber-800">Aplicar descuento ({{ $product->discountLabel() }})</span>
                        </label>
                        @endif
                        <div class="flex items-center gap-1.5">
                        <div class="flex shrink-0 items-center rounded-lg border border-slate-200">
                            <button type="button" tabindex="-1"
                                x-on:click="const input = $el.nextElementSibling; input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1); input.dispatchEvent(new Event('input'))"
                                class="px-2 py-1.5 text-slate-500 hover:bg-slate-100">−</button>
                            <input type="number" readonly wire:model="catalog_quantities.{{ $product->id }}" min="1" step="1"
                                class="w-8 border-0 bg-transparent p-0 text-center text-xs focus:outline-none focus:ring-0">
                            <button type="button" tabindex="-1"
                                x-on:click="const input = $el.previousElementSibling; input.value = (parseInt(input.value, 10) || 1) + 1; input.dispatchEvent(new Event('input'))"
                                class="px-2 py-1.5 text-slate-500 hover:bg-slate-100">+</button>
                        </div>
                        <button type="button" wire:click="addCatalogItem({{ $product->id }})" wire:loading.attr="disabled"
                            title="Agregar"
                            class="btn btn-primary btn-sm shrink-0 justify-center !px-2 !py-1.5 sm:min-w-0 sm:flex-1">
                            <svg class="h-4 w-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="hidden sm:inline !text-xs">Agregar</span>
                        </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div wire:loading.flex wire:target="loadMoreCatalogProducts" class="hidden items-center justify-center gap-2 py-4 text-xs font-medium text-slate-500">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Cargando más productos...
            </div>
            @if(! $catalog_has_more && $catalog_products->count() > 20)
            <p class="py-3 text-center text-xs text-slate-400">Has llegado al final del catálogo.</p>
            @endif
            @endif
        </div>
    </section>
    @endif

    @if($showItemModal)
    <x-ui.modal centered maxWidth="xl">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeItemModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">Editar ítem</h3>
            <button type="button" wire:click="closeItemModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Equipo</label>
                    <select wire:model="item_equipment_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('item_equipment_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Sin asignar</option>
                        @foreach($workOrder->equipments as $equipment)
                            <option value="{{ $equipment->id }}">{{ $equipment->select_label }}</option>
                        @endforeach
                    </select>
                    @error('item_equipment_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo de producto</label>
                    <select wire:model.live="product_type_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                        <option value="">Seleccionar…</option>
                        @foreach($product_types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Producto</label>
                    <select wire:model.live="product_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                        <option value="">Seleccionar…</option>
                        @foreach($edit_catalog_products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ col_money($product->sale_price) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Descripción <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="item_description" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('item_description') border-rose-400 bg-rose-50 @enderror">
                    @error('item_description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Cantidad</label>
                    <input type="number" wire:model="item_quantity" min="0.01" step="0.01" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Precio unitario</label>
                    <input type="number" wire:model="item_unit_price" min="0" step="0.01" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Descuento (%)</label>
                    <input type="number" wire:model="item_discount" min="0" max="100" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Notas del técnico</label>
                    <input type="text" wire:model="item_notes" placeholder="Observaciones internas…" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                </div>
            </div>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <button type="button" wire:click="closeItemModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
            <button type="button" wire:click="saveItem" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">Guardar</button>
        </div>
    </x-ui.modal>
    @endif

    @if($showDocumentModal)
    <x-ui.modal centered maxWidth="md">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeDocumentModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">
                {{ $editing_associated_document_id ? 'Editar documento asociado' : 'Asociar documento' }}
            </h3>
            <button type="button" wire:click="closeDocumentModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="saveDocumentClient" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Documento <span class="text-rose-500">*</span></label>
                    <select wire:model.live="selected_document_type_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('selected_document_type_id') border-rose-400 bg-rose-50 @enderror">
                        <option value="">Seleccionar…</option>
                        @forelse($available_associated_documents as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                        @empty
                        <option value="" disabled>No hay documentos disponibles</option>
                        @endforelse
                    </select>
                    @error('selected_document_type_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Valor <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="document_input_value" placeholder="Ej. número o referencia del documento"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('document_input_value') border-rose-400 bg-rose-50 @enderror">
                    @error('document_input_value') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="$toggle('send_invoice_value')"
                            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200 {{ $send_invoice_value ? 'bg-indigo-600' : 'bg-slate-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $send_invoice_value ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                        <span class="text-sm {{ $send_invoice_value ? 'font-medium text-indigo-700' : 'text-slate-500' }}">Enviar a facturación</span>
                    </div>
                    @if($send_invoice_value)
                    <p class="mt-1.5 text-xs text-slate-500">Este número de documento se usará para la facturación y será enviado a la DIAN.</p>
                    @endif
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closeDocumentModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                    <span wire:loading.remove wire:target="saveDocumentClient">Guardar</span>
                    <span wire:loading wire:target="saveDocumentClient">Guardando...</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif


    @if($preview_product)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-6"
        x-data="{ active: 0 }" x-on:keydown.escape.window="$wire.closeProductPreview()">
        <div class="flex max-h-full w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" @click.outside="$wire.closeProductPreview()">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ $preview_product->name }}</h3>
                <button type="button" wire:click="closeProductPreview" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="overflow-y-auto p-5">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <div class="relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            @forelse($preview_product->images as $i => $image)
                            <img x-show="active === {{ $i }}" x-cloak src="{{ $image->url }}" alt="Imagen {{ $i + 1 }} de {{ $preview_product->name }}" class="h-full w-full object-cover">
                            @empty
                            <div class="flex h-full w-full items-center justify-center text-slate-300">
                                <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 6h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z"/></svg>
                            </div>
                            @endforelse
                        </div>

                        @if($preview_product->images->count() > 1)
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($preview_product->images as $i => $image)
                            <button type="button" x-on:click="active = {{ $i }}"
                                :class="active === {{ $i }} ? 'ring-2 ring-indigo-500' : 'ring-1 ring-slate-200'"
                                class="h-14 w-14 shrink-0 overflow-hidden rounded-lg">
                                <img src="{{ $image->url }}" alt="Miniatura {{ $i + 1 }} de {{ $preview_product->name }}" class="h-full w-full object-cover">
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-1.5">
                            @if($preview_product->product_type)
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $preview_product->product_type->name }}</span>
                            @endif
                            @if($preview_product->product_category)
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $preview_product->product_category->name }}</span>
                            @endif
                            @if($preview_product->brand)
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $preview_product->brand->name }}</span>
                            @endif
                        </div>

                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">SKU</dt>
                                <dd class="font-mono text-slate-800">{{ $preview_product->sku }}</dd>
                            </div>
                            @if($preview_product->barcode)
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Código de barras</dt>
                                <dd class="font-mono text-slate-800">{{ $preview_product->barcode }}</dd>
                            </div>
                            @endif
                            @if($preview_product->unit)
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Unidad</dt>
                                <dd class="text-slate-800">{{ $preview_product->unit->name }} ({{ $preview_product->unit->symbol }})</dd>
                            </div>
                            @endif
                            <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-semibold">
                                <dt class="text-slate-700">Precio</dt>
                                @if($preview_product->hasDiscount() && ($catalog_apply_discount[$preview_product->id] ?? true))
                                <dd class="text-right">
                                    <span class="block text-xs font-normal text-slate-400 line-through">{{ col_money($preview_product->sale_price) }}</span>
                                    <span class="text-emerald-700">{{ col_money($preview_product->finalPrice()) }}</span>
                                </dd>
                                @else
                                <dd class="text-indigo-700">{{ col_money($preview_product->sale_price) }}</dd>
                                @endif
                            </div>
                        </dl>

                        @if($preview_product->hasDiscount())
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2">
                            <input type="checkbox" wire:model.live="catalog_apply_discount.{{ $preview_product->id }}"
                                class="h-4 w-4 rounded border-amber-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-amber-800">Aplicar descuento ({{ $preview_product->discountLabel() }})</span>
                        </label>
                        @endif

                        <div>
                            <h4 class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500">Descripción</h4>
                            <p class="whitespace-pre-line text-sm text-slate-700">{{ $preview_product->description ?: 'Sin descripción registrada.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-5 py-4">
                <div class="flex shrink-0 items-center rounded-lg border border-slate-200">
                    <button type="button" tabindex="-1"
                        x-on:click="const input = $el.nextElementSibling; input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1); input.dispatchEvent(new Event('input'))"
                        class="px-3 py-2 text-slate-500 hover:bg-slate-100">−</button>
                    <input type="number" readonly wire:model="catalog_quantities.{{ $preview_product->id }}" min="1" step="1"
                        class="w-10 border-0 bg-transparent p-0 text-center text-sm focus:outline-none focus:ring-0">
                    <button type="button" tabindex="-1"
                        x-on:click="const input = $el.previousElementSibling; input.value = (parseInt(input.value, 10) || 1) + 1; input.dispatchEvent(new Event('input'))"
                        class="px-3 py-2 text-slate-500 hover:bg-slate-100">+</button>
                </div>
                <button type="button" wire:click="addCatalogItem({{ $preview_product->id }})" class="btn btn-primary btn-sm flex-1 justify-center">
                    Agregar a la OT
                </button>
            </div>
        </div>
    </div>
    @endif
    @endif
    @if($showInvoiceModal)
    <x-ui.modal centered maxWidth="md">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeInvoiceModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">Facturar orden de trabajo</h3>
            <button type="button" wire:click="closeInvoiceModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
            <dl class="space-y-2 rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Orden</dt>
                    <dd class="font-mono font-semibold text-slate-900">{{ $workOrder->reference }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Cliente</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $workOrder->client?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-t border-slate-200 pt-2">
                    <dt class="font-medium text-slate-700">Total a facturar</dt>
                    <dd class="text-base font-bold text-indigo-700">{{ col_money($workOrder->total) }}</dd>
                </div>
            </dl>

            {{-- A nombre de quién sale el documento --}}
            <div class="rounded-xl border {{ $bill_to_final_consumer ? 'border-amber-200 bg-amber-50/60' : 'border-slate-200 bg-white' }} px-3.5 py-3 transition">
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" wire:model.live="bill_to_final_consumer"
                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold {{ $bill_to_final_consumer ? 'text-amber-900' : 'text-slate-700' }}">
                            Facturar a consumidor final
                        </span>
                        <span class="mt-0.5 block text-xs text-slate-500">
                            @if($bill_to_final_consumer)
                                La factura saldrá a nombre de «{{ config('dian.final_consumer.name') }}»
                                (NIT {{ config('dian.final_consumer.document_number') }}), no a nombre de
                                {{ $workOrder->client?->name ?? 'el cliente' }}.
                            @else
                                La factura saldrá a nombre de {{ $workOrder->client?->name ?? 'el cliente de la OT' }}.
                            @endif
                        </span>
                    </span>
                </label>
            </div>

            <div class="rounded-xl border {{ $send_to_dian ? 'border-indigo-200 bg-indigo-50/60' : 'border-slate-200 bg-white' }} px-3.5 py-3 transition">
                <div class="flex items-start gap-3">
                    <button type="button" wire:click="$toggle('send_to_dian')"
                        role="switch" aria-checked="{{ $send_to_dian ? 'true' : 'false' }}"
                        class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200 {{ $send_to_dian ? 'bg-indigo-600' : 'bg-slate-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $send_to_dian ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold {{ $send_to_dian ? 'text-indigo-800' : 'text-slate-700' }}">
                            Emitir enseguida ante la DIAN
                        </p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            @if($dian_setting)
                                Ambiente {{ $dian_setting->environment === 'production' ? 'de producción' : 'de pruebas' }} ·
                                próximo número <span class="font-mono">{{ $dian_setting->prefix }}{{ $dian_setting->upcomingConsecutive() }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                @if($send_to_dian)
                {{-- El medio de pago viaja dentro del documento y después ya no se
                     puede cambiar: registrar el pago más tarde no llega a la DIAN.
                     Se pregunta aquí, sin obligar: si no se sabe, va «instrumento
                     no definido», que es justo lo que la DIAN espera en ese caso. --}}
                <div class="mt-3 border-t border-indigo-200/70 pt-3">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">
                        Medio de pago <span class="font-normal text-slate-400">· opcional</span>
                    </label>
                    <select wire:model="dian_payment_means_code"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">No se sabe todavía</option>
                        @foreach(config('dian.payment_means') as $code => $label)
                            {{-- PHP convierte las claves numéricas del config en enteros: hay que comparar como texto. --}}
                            @if((string) $code !== '1')
                            <option value="{{ $code }}">{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">
                        Si lo dejas sin elegir, el documento sale como «instrumento no definido».
                        Después de emitir ya no se puede corregir.
                    </p>
                </div>
                @endif

                @if($dian_missing !== [])
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                    <p class="text-xs font-semibold text-amber-900">La DIAN podría rechazar el documento:</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5 text-[11px] text-amber-800">
                        @foreach($dian_missing as $item)
                        <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <p class="text-xs text-slate-500">
                @if($send_to_dian)
                    Se generará la factura y se enviará de una vez. Si el envío falla, la factura queda creada y podrás reintentar desde el panel DIAN de esta OT.
                @else
                    Solo se generará la factura. Podrás enviarla a la DIAN más tarde desde el panel DIAN de esta OT.
                @endif
            </p>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
            <button type="button" wire:click="closeInvoiceModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
            <button type="button" wire:click="confirmInvoice" wire:loading.attr="disabled" wire:target="confirmInvoice"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                <span wire:loading.remove wire:target="confirmInvoice">
                    {{ $send_to_dian ? 'Facturar y enviar a la DIAN' : 'Solo facturar' }}
                </span>
                <span wire:loading wire:target="confirmInvoice">
                    {{ $send_to_dian ? 'Facturando y enviando…' : 'Facturando…' }}
                </span>
            </button>
        </div>
    </x-ui.modal>
    @endif
</div>
